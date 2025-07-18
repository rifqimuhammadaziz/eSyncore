<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'department',
        'hire_date',
        'termination_date',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'salary',
        'bank_account',
        'bank_name',
        'tax_id',
        'user_id',
        'notes',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
        'hire_date' => 'date',
        'termination_date' => 'date',
    ];
    
    /**
     * Get the user associated with this employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get purchase orders created by this employee
     */
    public function createdPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }
    
    /**
     * Get purchase orders approved by this employee
     */
    public function approvedPurchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'approved_by');
    }
    
    /**
     * Get sales orders created by this employee
     */
    public function createdSalesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'created_by');
    }
    
    /**
     * Get sales orders approved by this employee
     */
    public function approvedSalesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'approved_by');
    }
    
    /**
     * Get the full name of the employee
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
