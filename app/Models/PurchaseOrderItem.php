<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'purchase_order_id',
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
        'received_quantity',
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
        'received_quantity' => 'decimal:2',
    ];
    
    /**
     * Get the purchase order this item belongs to
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
    
    /**
     * Get the product for this purchase order item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get status options for purchase order items
     */
    public static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'received_partial' => 'Partially Received',
            'received_complete' => 'Fully Received',
            'cancelled' => 'Cancelled',
        ];
    }
}
