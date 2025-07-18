<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'manager_name',
        'is_active',
        'notes',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the inventory items for this warehouse
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
    
    /**
     * Get all products available in this warehouse
     */
    public function products()
    {
        return Product::whereHas('inventories', function ($query) {
            $query->where('warehouse_id', $this->id);
        });
    }
    
    /**
     * Get inventory items that need reordering
     */
    public function itemsNeedingReorder()
    {
        return $this->inventoryItems()
            ->whereRaw('quantity_available <= reorder_point')
            ->with('product')
            ->get();
    }
    
    /**
     * Get inventory items with low stock
     */
    public function lowStockItems()
    {
        return $this->inventoryItems()
            ->whereRaw('quantity_available <= minimum_stock')
            ->with('product')
            ->get();
    }
}
