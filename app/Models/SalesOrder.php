<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'so_number',
        'customer_id',
        'order_date',
        'expected_shipping_date',
        'shipping_date',
        'status',
        'subtotal',
        'tax',
        'discount',
        'shipping_cost',
        'grand_total',
        'payment_status',
        'payment_due_date',
        'payment_terms',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'shipping_method',
        'tracking_number',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];
    
    protected $casts = [
        'order_date' => 'date',
        'expected_shipping_date' => 'date',
        'shipping_date' => 'date',
        'payment_due_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];
    
    /**
     * Get the customer for this sales order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    /**
     * Get the items for this sales order
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }
    
    /**
     * Get the employee who created this sales order
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    
    /**
     * Get the employee who approved this sales order
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
    
    /**
     * Define status options for sales orders
     */
    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'processing' => 'Processing',
            'shipped_partial' => 'Partially Shipped',
            'shipped_complete' => 'Fully Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
    }
    
    /**
     * Define payment status options
     */
    public static function getPaymentStatusOptions(): array
    {
        return [
            'unpaid' => 'Unpaid',
            'partial' => 'Partially Paid',
            'paid' => 'Paid',
        ];
    }
    
    /**
     * Define shipping method options
     */
    public static function getShippingMethodOptions(): array
    {
        return [
            'standard' => 'Standard Shipping',
            'express' => 'Express Shipping',
            'overnight' => 'Overnight Shipping',
            'local_pickup' => 'Local Pickup',
            'free_shipping' => 'Free Shipping',
        ];
    }
}
