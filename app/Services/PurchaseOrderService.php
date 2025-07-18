<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    /**
     * Process a purchase order receipt
     * 
     * @param PurchaseOrder $purchaseOrder
     * @param array $receivedItems Array of item_id => quantity_received
     * @param int|null $warehouseId
     * @param array $options Additional options like batch numbers, expiry dates, etc.
     * @return bool
     */
    public function processPurchaseOrderReceipt(
        PurchaseOrder $purchaseOrder,
        array $receivedItems,
        ?int $warehouseId = null,
        array $options = []
    ): bool {
        // If warehouse ID not specified, use the default from the purchase order
        $warehouseId = $warehouseId ?: $purchaseOrder->warehouse_id;
        
        if (!$warehouseId) {
            Log::error('No warehouse specified for purchase order receipt', [
                'purchase_order_id' => $purchaseOrder->id,
            ]);
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            foreach ($receivedItems as $itemId => $receivedQty) {
                if ($receivedQty <= 0) continue;
                
                $item = PurchaseOrderItem::find($itemId);
                
                if (!$item || $item->purchase_order_id != $purchaseOrder->id) {
                    throw new Exception("Item with ID {$itemId} does not belong to this purchase order");
                }
                
                // Calculate how much more can be received
                $maxReceivable = $item->quantity - $item->received_quantity;
                
                if ($receivedQty > $maxReceivable) {
                    // Cap at maximum receivable quantity
                    $receivedQty = $maxReceivable;
                }
                
                if ($receivedQty <= 0) continue;
                
                // Update the item's received quantity
                $item->received_quantity += $receivedQty;
                
                // Update item status
                if ($item->received_quantity >= $item->quantity) {
                    $item->status = 'received';
                } else {
                    $item->status = 'partial';
                }
                
                $item->save();
                
                // Add to inventory
                $inventory = Inventory::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity_available' => 0,
                        'quantity_reserved' => 0,
                    ]
                );
                
                $inventory->quantity_available += $receivedQty;
                $inventory->save();
                
                // Create inventory transaction record
                $batchNumber = $options['batch_numbers'][$itemId] ?? null;
                $expiryDate = $options['expiry_dates'][$itemId] ?? null;
                
                $this->createInventoryTransaction(
                    $item->product_id,
                    $warehouseId,
                    'purchase',
                    'purchase_order',
                    $purchaseOrder->id,
                    $receivedQty,
                    "Purchase Order: {$purchaseOrder->po_number}",
                    $batchNumber,
                    $expiryDate
                );
            }
            
            // Update the purchase order status
            $this->updatePurchaseOrderStatus($purchaseOrder);
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing purchase order receipt: ' . $e->getMessage(), [
                'purchase_order_id' => $purchaseOrder->id,
                'error' => $e,
            ]);
            
            return false;
        }
    }
    
    /**
     * Update a purchase order's status based on its items
     * 
     * @param PurchaseOrder $purchaseOrder
     * @return void
     */
    private function updatePurchaseOrderStatus(PurchaseOrder $purchaseOrder): void
    {
        // If it's not approved yet, don't change the status
        if ($purchaseOrder->status !== 'approved') {
            return;
        }
        
        $allItems = $purchaseOrder->items;
        $totalItems = $allItems->count();
        
        if ($totalItems === 0) {
            return;
        }
        
        $receivedItems = $allItems->where('status', 'received')->count();
        $partialItems = $allItems->where('status', 'partial')->count();
        
        if ($receivedItems === $totalItems) {
            $purchaseOrder->status = 'received';
        } elseif ($receivedItems > 0 || $partialItems > 0) {
            $purchaseOrder->status = 'partial';
        }
        
        $purchaseOrder->save();
    }
    
    /**
     * Create an inventory transaction record
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param string $transactionType
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param float $quantity
     * @param string|null $notes
     * @param string|null $batchNumber
     * @param string|null $expiryDate
     * @return InventoryTransaction
     */
    private function createInventoryTransaction(
        int $productId,
        int $warehouseId,
        string $transactionType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        float $quantity = 0,
        ?string $notes = null,
        ?string $batchNumber = null,
        ?string $expiryDate = null
    ): InventoryTransaction {
        return InventoryTransaction::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'transaction_type' => $transactionType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity' => $quantity,
            'batch_number' => $batchNumber,
            'expiry_date' => $expiryDate,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }
}
