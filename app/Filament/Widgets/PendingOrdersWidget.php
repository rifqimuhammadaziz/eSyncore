<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

class PendingOrdersWidget extends Widget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Pending Orders';
    
    protected static string $view = 'filament.widgets.pending-orders-widget';
    
    /**
     * Format currency value according to company settings
     *
     * @param float $amount
     * @return string
     */
    public function formatCurrency(float $amount): string
    {
        // Use company settings for currency if available, or default to IDR
        $company = Company::first();
        
        $currencySymbol = $company ? $company->currency_symbol : 'Rp';
        $thousandSeparator = $company ? $company->thousand_separator : '.';
        $decimalSeparator = $company ? $company->decimal_separator : ',';
        $decimalPrecision = $company ? $company->decimal_precision : 0; // IDR typically uses 0 decimals
        
        $formatted = number_format($amount, $decimalPrecision, $decimalSeparator, $thousandSeparator);
        
        return $currencySymbol . ' ' . $formatted;
    }
    
    public function getPendingSalesOrders(): Collection
    {
        return SalesOrder::where('status', 'pending')
            ->with('customer')
            ->latest('created_at')
            ->limit(5)
            ->get();
    }
    
    public function getPendingPurchaseOrders(): Collection
    {
        return PurchaseOrder::where('status', 'pending')
            ->with('supplier')
            ->latest('created_at')
            ->limit(5)
            ->get();
    }
}
