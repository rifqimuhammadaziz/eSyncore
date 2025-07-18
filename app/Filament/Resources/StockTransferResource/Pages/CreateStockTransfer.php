<?php

namespace App\Filament\Resources\StockTransferResource\Pages;

use App\Filament\Resources\StockTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockTransfer extends CreateRecord
{
    protected static string $resource = StockTransferResource::class;
    
    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();
        return static::getModel()::create($data);
    }
}
