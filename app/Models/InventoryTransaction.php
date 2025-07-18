<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'reference_type',
        'reference_id',
        'transaction_type',
        'quantity',
        'batch_number',
        'expiry_date',
        'notes',
        'created_by',
    ];
    
    protected $casts = [
        'quantity' => 'decimal:2',
        'expiry_date' => 'date',
    ];
    
    /**
     * Get the product associated with this transaction
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the warehouse associated with this transaction
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    /**
     * Get the employee who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    
    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo();
    }
    
    /**
     * Define transaction type options
     */
    public static function getTransactionTypeOptions(): array
    {
        return [
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'adjustment_add' => 'Adjustment (Add)',
            'adjustment_remove' => 'Adjustment (Remove)',
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'sales' => 'Sales Order',
            'purchase' => 'Purchase Order',
            'return_in' => 'Return (In)',
            'return_out' => 'Return (Out)',
        ];
    }
    
    /**
     * Define reference type options
     */
    public static function getReferenceTypeOptions(): array
    {
        return [
            'purchase_order' => 'Purchase Order',
            'sales_order' => 'Sales Order',
            'stock_adjustment' => 'Stock Adjustment',
            'stock_transfer' => 'Stock Transfer',
            'manual' => 'Manual Entry',
        ];
    }
    
    /**
     * Determine if this is an incoming transaction
     */
    public function getIsIncomingAttribute(): bool
    {
        return in_array($this->transaction_type, [
            'stock_in', 'adjustment_add', 'transfer_in', 'purchase', 'return_in'
        ]);
    }
    
    /**
     * Get the absolute quantity (always positive)
     */
    public function getAbsoluteQuantityAttribute(): float
    {
        return abs((float) $this->quantity);
    }
}
