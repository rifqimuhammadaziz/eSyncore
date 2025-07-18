<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewOrders')
                ->label('View Orders')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(fn (Supplier $record): string => 
                    route('filament.portal.resources.purchase-orders.index', [
                        'tableFilters[supplier_id][value]' => $record->id,
                    ])
                )
                ->visible(fn (Supplier $record): bool => 
                    PurchaseOrder::where('supplier_id', $record->id)->exists()
                ),
                
            Actions\Action::make('createOrder')
                ->label('New Order')
                ->icon('heroicon-o-plus')
                ->url(fn (Supplier $record): string => 
                    route('filament.portal.resources.purchase-orders.create', [
                        'supplier_id' => $record->id,
                    ])
                ),
                
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Supplier Deleted')
                        ->body('The supplier has been deleted successfully.')
                ),
        ];
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Supplier Updated')
            ->body('The supplier information has been updated successfully.')
            ->icon('heroicon-o-building-storefront');
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Add any data transformations if needed
        return $data;
    }
}
