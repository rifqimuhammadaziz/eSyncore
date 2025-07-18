<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // General Company Information
        'name',
        'legal_name',
        'tax_id',
        'registration_number',
        'industry',
        'description',
        'logo',
        'favicon',
        
        // Contact & Address Information
        'email',
        'phone',
        'fax',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'google_maps_url',
        
        // Locale & Currency Settings
        'language',
        'timezone',
        'date_format',
        'time_format',
        'currency_code',
        'currency_symbol',
        'currency_position',
        'decimal_precision',
        'thousand_separator',
        'decimal_separator',
        
        // Business Settings
        'fiscal_year_start',
        'accounting_method',
        'default_payment_terms',
        'invoice_due_days',
        'quote_valid_days',
        
        // System & Appearance
        'primary_color',
        'secondary_color',
        'theme_mode',
        'enable_notifications',
        'is_active',
        
        // Company Contact Person
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
        'contact_person_position',
        
        // Social Media & Additional Settings
        'social_media',
        'additional_settings',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'social_media' => 'array',
        'additional_settings' => 'array',
        'enable_notifications' => 'boolean',
        'is_active' => 'boolean',
        'decimal_precision' => 'integer',
        'invoice_due_days' => 'integer',
        'quote_valid_days' => 'integer',
    ];
    
    /**
     * Get the formatted currency symbol based on position
     */
    public function getFormattedCurrencyAttribute(): array
    {
        return [
            'symbol' => $this->currency_symbol ?? '$',
            'position' => $this->currency_position ?? 'before',
            'decimal_separator' => $this->decimal_separator ?? '.',
            'thousand_separator' => $this->thousand_separator ?? ',',
            'precision' => $this->decimal_precision ?? 2,
        ];
    }
    
    /**
     * Format a number as currency according to company settings
     */
    public function formatCurrency(float $amount): string
    {
        $formatted = number_format(
            $amount,
            $this->decimal_precision ?? 2,
            $this->decimal_separator ?? '.',
            $this->thousand_separator ?? ','
        );
        
        if (($this->currency_position ?? 'before') === 'before') {
            return ($this->currency_symbol ?? '$') . $formatted;
        } else {
            return $formatted . ($this->currency_symbol ?? '$');
        }
    }
}
