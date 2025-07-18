<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\Supplier;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
    
    protected function getCreatedNotification(): ?Notification
    {
        $supplier = $this->record;
        
        return Notification::make()
            ->success()
            ->title('Supplier Added')
            ->body("New supplier {$supplier->name} has been created successfully.")
            ->icon('heroicon-o-building-storefront');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Add any data transformations if needed
        return $data;
    }
}
