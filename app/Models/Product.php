<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'sku',
        'name',
        'description',
        'purchase_price',
        'selling_price',
        'category_id',
        'unit',
        'barcode',
        'is_active',
        'image_path',
        'attributes'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'attributes' => AsArrayObject::class,
    ];
    
    /**
     * Get the category that the product belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    
    /**
     * Get the inventory records for this product
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
    
    /**
     * Get total quantity available across all warehouses
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->inventories()->sum('quantity_available');
    }
    
    /**
     * Get the purchase order items for this product
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
    
    /**
     * Get the sales order items for this product
     */
    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
