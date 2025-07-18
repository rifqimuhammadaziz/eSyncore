<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use App\Models\StockTransfer;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStockTransfer extends ViewRecord
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn (StockTransfer $record): bool => 
                    $record->status !== StockTransfer::STATUS_APPROVED && 
                    $record->status !== StockTransfer::STATUS_COMPLETED),
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
            Actions\Action::make('printTransfer')
                ->label('Print Transfer')
                ->icon('heroicon-o-printer')
                ->url(fn (StockTransfer $record): string => route('stock-transfers.print', $record))
                ->openUrlInNewTab(),
        ];
    }
}
