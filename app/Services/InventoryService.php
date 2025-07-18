<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Process inventory for a sales order
     * 
     * @param SalesOrder $salesOrder
     * @return bool
     */
    public function processSalesOrderInventory(SalesOrder $salesOrder): bool
    {
        // Only process if the order is approved
        if ($salesOrder->status !== 'approved') {
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            foreach ($salesOrder->items as $item) {
                // Check if we need to allocate stock for this item
                if ($item->shipped_quantity < $item->quantity) {
                    $remainingToShip = $item->quantity - $item->shipped_quantity;
                    $this->allocateStockForSalesItem($item, $remainingToShip);
                }
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing sales order inventory: ' . $e->getMessage(), [
                'sales_order_id' => $salesOrder->id,
                'error' => $e
            ]);
            
            return false;
        }
    }
    
    /**
     * Allocate stock for a sales order item
     * 
     * @param SalesOrderItem $item
     * @param float $quantity
     * @return bool
     */
    private function allocateStockForSalesItem(SalesOrderItem $item, float $quantity): bool
    {
        // Find warehouses with available stock for this product
        $inventories = Inventory::where('product_id', $item->product_id)
            ->where('quantity_available', '>', 0)
            ->orderBy('warehouse_id') // Could use FIFO, LIFO, or other strategies here
            ->get();
            
        $remainingToAllocate = $quantity;
        
        foreach ($inventories as $inventory) {
            // Skip if we've allocated all needed
            if ($remainingToAllocate <= 0) break;
            
            // Calculate how much we can take from this inventory
            $quantityToTake = min($inventory->quantity_available, $remainingToAllocate);
            
            if ($quantityToTake <= 0) continue;
            
            // Update the inventory
            $inventory->quantity_available -= $quantityToTake;
            $inventory->save();
            
            // Create an inventory transaction record
            $this->createInventoryTransaction(
                $item->product_id,
                $inventory->warehouse_id,
                'sale',
                'sales_order',
                $item->sales_order_id,
                -$quantityToTake,
                "Sales order: {$item->salesOrder->so_number}"
            );
            
            // Update the shipped quantity for this item
            $item->shipped_quantity += $quantityToTake;
            $item->save();
            
            $remainingToAllocate -= $quantityToTake;
        }
        
        // Update the status of the item
        if ($item->shipped_quantity >= $item->quantity) {
            $item->status = 'completed';
        } elseif ($item->shipped_quantity > 0) {
            $item->status = 'partial';
        } else {
            $item->status = 'pending';
        }
        $item->save();
        
        // If we couldn't allocate all requested quantity, log it
        if ($remainingToAllocate > 0) {
            Log::warning('Insufficient stock to fully allocate sales order item', [
                'sales_order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'requested' => $quantity,
                'allocated' => $quantity - $remainingToAllocate,
                'unallocated' => $remainingToAllocate
            ]);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Process a stock adjustment and update inventory
     * 
     * @param StockAdjustment $adjustment
     * @return bool
     */
    public function processStockAdjustment(StockAdjustment $adjustment): bool
    {
        // Only process approved adjustments
        if ($adjustment->status !== 'approved') {
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            foreach ($adjustment->items as $item) {
                // Find or create inventory record
                $inventory = Inventory::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $adjustment->warehouse_id,
                    ],
                    [
                        'quantity_available' => 0,
                        'quantity_reserved' => 0,
                    ]
                );
                
                // Update inventory with the adjusted quantity
                $inventory->quantity_available = $item->new_quantity;
                $inventory->save();
                
                // Create transaction records
                $transactionType = $item->quantity >= 0 ? 'adjustment_add' : 'adjustment_remove';
                $this->createInventoryTransaction(
                    $item->product_id,
                    $adjustment->warehouse_id,
                    $transactionType,
                    'stock_adjustment',
                    $adjustment->id,
                    $item->quantity,
                    "Stock adjustment: {$adjustment->adjustment_number} - {$adjustment->reason}"
                );
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing stock adjustment: ' . $e->getMessage(), [
                'adjustment_id' => $adjustment->id,
                'error' => $e
            ]);
            
            return false;
        }
    }
    
    /**
     * Transfer stock between warehouses
     * 
     * @param Product $product
     * @param Warehouse $sourceWarehouse
     * @param Warehouse $destinationWarehouse
     * @param float $quantity
     * @param array $options Additional options like batch number, expiry, etc.
     * @return bool
     */
    public function transferStock(
        Product $product, 
        Warehouse $sourceWarehouse, 
        Warehouse $destinationWarehouse, 
        float $quantity, 
        array $options = []
    ): bool {
        if ($quantity <= 0) {
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            // Check source inventory
            $sourceInventory = Inventory::where('product_id', $product->id)
                ->where('warehouse_id', $sourceWarehouse->id)
                ->first();
                
            if (!$sourceInventory || $sourceInventory->quantity_available < $quantity) {
                throw new Exception('Insufficient stock in source warehouse');
            }
            
            // Reduce source inventory
            $sourceInventory->quantity_available -= $quantity;
            $sourceInventory->save();
            
            // Create outgoing transaction
            $this->createInventoryTransaction(
                $product->id,
                $sourceWarehouse->id,
                'transfer_out',
                'stock_transfer',
                null,
                -$quantity,
                "Transfer to {$destinationWarehouse->name}",
                $options['batch_number'] ?? null,
                $options['expiry_date'] ?? null,
                $options['created_by'] ?? null
            );
            
            // Increase destination inventory
            $destInventory = Inventory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $destinationWarehouse->id,
                ],
                [
                    'quantity_available' => 0,
                    'quantity_reserved' => 0,
                ]
            );
            
            $destInventory->quantity_available += $quantity;
            $destInventory->save();
            
            // Create incoming transaction
            $this->createInventoryTransaction(
                $product->id,
                $destinationWarehouse->id,
                'transfer_in',
                'stock_transfer',
                null,
                $quantity,
                "Transfer from {$sourceWarehouse->name}",
                $options['batch_number'] ?? null,
                $options['expiry_date'] ?? null,
                $options['created_by'] ?? null
            );
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error transferring stock: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'source_warehouse_id' => $sourceWarehouse->id,
                'destination_warehouse_id' => $destinationWarehouse->id,
                'quantity' => $quantity,
                'error' => $e
            ]);
            
            return false;
        }
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
     * @param int|null $createdBy
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
        ?string $expiryDate = null,
        ?int $createdBy = null
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
            'created_by' => $createdBy ?? auth()->id(),
        ]);
    }
}
