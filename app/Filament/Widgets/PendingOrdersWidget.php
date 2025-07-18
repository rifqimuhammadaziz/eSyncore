<?php

namespace App\Filament\Widgets;

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
