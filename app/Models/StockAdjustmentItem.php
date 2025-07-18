<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'current_quantity',
        'new_quantity',
        'quantity',
        'batch_number',
        'expiry_date',
        'reason',
        'notes',
    ];
    
    protected $casts = [
        'current_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
        'quantity' => 'decimal:2', // The adjustment amount (can be positive or negative)
        'expiry_date' => 'date',
    ];
    
    /**
     * Get the stock adjustment that owns this item
     */
    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }
    
    /**
     * Get the product for this adjustment item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Set the quantity attribute based on current and new quantity
     */
    public static function booted()
    {
        static::saving(function ($model) {
            if ($model->current_quantity !== null && $model->new_quantity !== null) {
                $model->quantity = $model->new_quantity - $model->current_quantity;
            }
        });
    }
}
