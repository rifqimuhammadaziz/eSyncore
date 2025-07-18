<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'adjustment_number',
        'warehouse_id',
        'adjustment_date',
        'reference_number',
        'reason',
        'notes',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];
    
    protected $casts = [
        'adjustment_date' => 'date',
        'approved_at' => 'datetime',
    ];
    
    /**
     * Get the warehouse for this adjustment
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
    
    /**
     * Get the items for this adjustment
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
    
    /**
     * Get the employee who created this adjustment
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    
    /**
     * Get the employee who approved this adjustment
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
    
    /**
     * Define status options for stock adjustments
     */
    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'cancelled' => 'Cancelled',
        ];
    }
    
    /**
     * Define reason options for stock adjustments
     */
    public static function getReasonOptions(): array
    {
        return [
            'physical_count' => 'Physical Count',
            'damage' => 'Damaged Goods',
            'expiry' => 'Expired Items',
            'theft' => 'Theft/Loss',
            'return' => 'Customer Return',
            'supplier_return' => 'Supplier Return',
            'other' => 'Other',
        ];
    }
    
    /**
     * Create inventory transactions for all items in this adjustment
     */
    public function createInventoryTransactions(): void
    {
        foreach ($this->items as $item) {
            $transactionType = $item->quantity > 0 ? 'adjustment_add' : 'adjustment_remove';
            
            InventoryTransaction::create([
                'product_id' => $item->product_id,
                'warehouse_id' => $this->warehouse_id,
                'reference_type' => 'stock_adjustment',
                'reference_id' => $this->id,
                'transaction_type' => $transactionType,
                'quantity' => $item->quantity,
                'batch_number' => $item->batch_number,
                'notes' => "Stock adjustment: {$this->adjustment_number} - {$this->reason}",
                'created_by' => $this->created_by,
            ]);
        }
    }
}
