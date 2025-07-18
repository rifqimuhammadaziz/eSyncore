<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'tax_percentage',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'subtotal',
        'total',
        'shipped_quantity',
        'status',
    ];
    
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'shipped_quantity' => 'decimal:2',
    ];
    
    /**
     * Get the sales order this item belongs to
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
    
    /**
     * Get the product for this sales order item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get status options for sales order items
     */
    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'shipped_partial' => 'Partially Shipped',
            'shipped_complete' => 'Fully Shipped',
            'cancelled' => 'Cancelled',
        ];
    }
}
