<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (PurchaseOrder $record): bool => $record->status === 'draft' || $record->status === 'pending'),
            Actions\Action::make('receive')
                ->label('Receive Items')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->url(fn (PurchaseOrder $record): string => PurchaseOrderResource::getUrl('receive', ['record' => $record]))
                ->visible(fn (PurchaseOrder $record): bool => 
                    $record->status === 'approved' || $record->status === 'partial'),
            Actions\Action::make('print')
                ->label('Print PO')
                ->icon('heroicon-o-printer')
                ->url(fn (PurchaseOrder $record): string => route('purchase-orders.print', ['id' => $record->id]))
                ->openUrlInNewTab()
                ->visible(fn (PurchaseOrder $record): bool => $record->status !== 'draft'),
        ];
    }
}
