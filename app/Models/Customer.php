<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'credit_limit',
        'notes',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:2',
    ];
    
    /**
     * Get the sales orders for this customer
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
    
    /**
     * Calculate total outstanding balance for this customer
     */
    public function getOutstandingBalanceAttribute()
    {
        return $this->salesOrders()
            ->where('payment_status', '!=', 'paid')
            ->sum('grand_total');
    }
}
