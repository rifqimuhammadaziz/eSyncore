<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use App\Models\StockTransfer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockTransfer extends EditRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\Action::make('approve')
                ->label('Approve & Process')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (StockTransfer $record): bool => 
                    $record->status === StockTransfer::STATUS_PENDING || 
                    $record->status === StockTransfer::STATUS_DRAFT)
                ->action(function () {
                    $record = $this->getRecord();
                    $record->status = StockTransfer::STATUS_APPROVED;
                    $record->approved_by = auth()->id();
                    $record->approved_at = now();
                    $record->save();
                    
                    // Process the transfer
                    $record->processTransfer();
                    
                    $this->refreshFormData(['status', 'approved_by', 'approved_at']);
                    $this->notify('success', 'Stock transfer approved and processed successfully');
                }),
            Actions\DeleteAction::make()
                ->visible(fn (StockTransfer $record): bool => 
                    $record->status !== StockTransfer::STATUS_APPROVED && 
                    $record->status !== StockTransfer::STATUS_COMPLETED),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent changes to approved transfers
        $record = $this->getRecord();
        if ($record->status === StockTransfer::STATUS_APPROVED || $record->status === StockTransfer::STATUS_COMPLETED) {
            $data['transfer_number'] = $record->transfer_number;
            $data['source_warehouse_id'] = $record->source_warehouse_id;
            $data['destination_warehouse_id'] = $record->destination_warehouse_id;
            $data['transfer_date'] = $record->transfer_date;
            $data['status'] = $record->status;
            $data['approved_by'] = $record->approved_by;
            $data['approved_at'] = $record->approved_at;
        }
        
        return $data;
    }
}
