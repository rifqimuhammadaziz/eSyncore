<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Get current month stats
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        
        // Get previous month stats
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        // Calculate sales
        $currentMonthSales = SalesOrder::whereBetween('order_date', [$currentMonthStart, $currentMonthEnd])
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
        
        $previousMonthSales = SalesOrder::whereBetween('order_date', [$previousMonthStart, $previousMonthEnd])
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
        
        $salesDifference = $previousMonthSales ? (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100 : 0;
        
        // Calculate purchases
        $currentMonthPurchases = PurchaseOrder::whereBetween('po_date', [$currentMonthStart, $currentMonthEnd])
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
        
        $previousMonthPurchases = PurchaseOrder::whereBetween('po_date', [$previousMonthStart, $previousMonthEnd])
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');
        
        $purchasesDifference = $previousMonthPurchases ? (($currentMonthPurchases - $previousMonthPurchases) / $previousMonthPurchases) * 100 : 0;
        
        // Count suppliers and customers
        $suppliersCount = Supplier::where('is_active', true)->count();
        $customersCount = Customer::where('is_active', true)->count();
        
        // Use company settings for currency if available, or default to IDR
        $company = \App\Models\Company::first();
        $currencySymbol = $company ? $company->currency_symbol : 'Rp';
        $thousandSeparator = $company ? $company->thousand_separator : '.';
        $decimalSeparator = $company ? $company->decimal_separator : ',';
        $decimalPrecision = $company ? $company->decimal_precision : 0; // IDR typically uses 0 decimals
        
        // Helper function to format currency according to company settings
        $formatCurrency = function ($amount) use ($currencySymbol, $decimalPrecision, $thousandSeparator, $decimalSeparator) {
            $formatted = number_format($amount, $decimalPrecision, $decimalSeparator, $thousandSeparator);
            return $currencySymbol . ' ' . $formatted;
        };
        
        // Format percentage values with 1 decimal place for consistency
        $formatPercentage = function ($value) {
            return number_format($value, 1) . '%';
        };
        
        return [
            Stat::make('Monthly Sales', $formatCurrency($currentMonthSales))
                ->description($salesDifference >= 0 
                    ? $formatPercentage($salesDifference) . ' increase' 
                    : $formatPercentage(abs($salesDifference)) . ' decrease')
                ->descriptionIcon($salesDifference >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesDifference >= 0 ? 'success' : 'danger')
                ->chart([
                    $previousMonthSales / 1000,
                    $currentMonthSales / 1000,
                ]),
                
            Stat::make('Monthly Purchases', $formatCurrency($currentMonthPurchases))
                ->description($purchasesDifference >= 0 
                    ? $formatPercentage($purchasesDifference) . ' increase' 
                    : $formatPercentage(abs($purchasesDifference)) . ' decrease')
                ->descriptionIcon($purchasesDifference >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($purchasesDifference >= 0 ? 'warning' : 'success')
                ->chart([
                    $previousMonthPurchases / 1000,
                    $currentMonthPurchases / 1000,
                ]),
                
            Stat::make('Profit Margin', $formatCurrency($currentMonthSales - $currentMonthPurchases))
                ->description('Current Month')
                ->color('success'),
                
            Stat::make('Active Suppliers', $suppliersCount)
                ->icon('heroicon-m-truck'),
                
            Stat::make('Active Customers', $customersCount)
                ->icon('heroicon-m-user-group'),
        ];
    }
}
