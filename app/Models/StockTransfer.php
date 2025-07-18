<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'source_warehouse_id',
        'destination_warehouse_id',
        'transfer_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get all available statuses for stock transfers
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * Get the source warehouse
     */
    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    /**
     * Get the destination warehouse
     */
    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    /**
     * Get the items in this transfer
     */
    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Get the user who created this transfer
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * Get the user who approved this transfer
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Generate a new transfer number
     *
     * @return string
     */
    public static function generateTransferNumber(): string
    {
        $lastTransfer = self::orderBy('id', 'desc')->first();
        $lastId = $lastTransfer ? $lastTransfer->id : 0;
        $nextId = $lastId + 1;
        
        return 'TRF' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Process this transfer to update inventory
     * 
     * @param InventoryService $inventoryService
     * @return bool
     */
    public function processTransfer(): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $inventoryService = app(App\Services\InventoryService::class);
        $success = true;

        foreach ($this->items as $item) {
            $result = $inventoryService->transferStock(
                $item->product,
                $this->sourceWarehouse,
                $this->destinationWarehouse,
                $item->quantity,
                [
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date,
                    'created_by' => $this->approved_by,
                ]
            );

            if (!$result) {
                $success = false;
            }
        }

        if ($success) {
            $this->status = self::STATUS_COMPLETED;
            $this->save();
        }

        return $success;
    }
}
