<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',  // Changed from order_date to po_date
        'expected_delivery_date',
        'delivery_date',
        'status',
        'subtotal',
        'tax',
        'discount',
        'shipping_cost',
        'grand_total',
        'payment_status',
        'payment_due_date',
        'payment_terms',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
    ];
    
    protected $casts = [
        'po_date' => 'date',  // Changed from order_date to po_date
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
        'payment_due_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];
    
    /**
     * Get the supplier for this purchase order
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    
    /**
     * Get the items for this purchase order
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    
    /**
     * Get the employee who created this purchase order
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    
    /**
     * Get the employee who approved this purchase order
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
    
    /**
     * Define status options for purchase orders
     */
    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'ordered' => 'Ordered',
            'received_partial' => 'Partially Received',
            'received_complete' => 'Fully Received',
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
}
