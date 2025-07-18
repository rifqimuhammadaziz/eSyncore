<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockTransfers extends ListRecords
{
    protected static string $resource = StockTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
