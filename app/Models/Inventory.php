<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity_available',
        'quantity_reserved',
        'minimum_stock',
        'reorder_point',
        'bin_location',
        'last_counted_date',
        'expiry_date',
        'batch_number',
        'notes',
    ];
    
    protected $casts = [
        'quantity_available' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'last_counted_date' => 'date',
        'expiry_date' => 'date',
    ];
    
    /**
     * Get the product that this inventory belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the warehouse that this inventory belongs to
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    /**
     * Get the quantity on hand (available minus reserved)
     */
    public function getQuantityOnHandAttribute(): float
    {
        return (float)$this->quantity_available - (float)$this->quantity_reserved;
    }
    
    /**
     * Check if this inventory item needs to be reordered
     */
    public function getNeedsReorderAttribute(): bool
    {
        return (float)$this->quantity_available <= (float)$this->reorder_point;
    }
    
    /**
     * Check if this inventory item is below the minimum stock level
     */
    public function getLowStockAttribute(): bool
    {
        return (float)$this->quantity_available <= (float)$this->minimum_stock;
    }
}
