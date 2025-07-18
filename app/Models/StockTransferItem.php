<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Get the stock transfer this item belongs to
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Get the product being transferred
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if the requested quantity is available in the source warehouse
     * 
     * @return bool
     */
    public function isQuantityAvailable(): bool
    {
        // Get source warehouse from parent stock transfer
        $sourceWarehouseId = $this->stockTransfer->source_warehouse_id;
        
        // Check inventory for this product in the source warehouse
        $inventory = Inventory::where('product_id', $this->product_id)
            ->where('warehouse_id', $sourceWarehouseId)
            ->first();
        
        if (!$inventory) {
            return false;
        }
        
        return $inventory->quantity_available >= $this->quantity;
    }
    
    /**
     * Get available quantity for this product in source warehouse
     * 
     * @return float
     */
    public function getAvailableQuantity(): float
    {
        // Get source warehouse from parent stock transfer
        $sourceWarehouseId = $this->stockTransfer->source_warehouse_id;
        
        // Get inventory for this product in the source warehouse
        $inventory = Inventory::where('product_id', $this->product_id)
            ->where('warehouse_id', $sourceWarehouseId)
            ->first();
        
        if (!$inventory) {
            return 0;
        }
        
        return $inventory->quantity_available;
    }
}
