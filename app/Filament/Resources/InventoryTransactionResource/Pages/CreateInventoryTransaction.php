<?php

namespace App\Filament\Resources\InventoryTransactionResource\Pages;

use App\Filament\Resources\InventoryTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryTransaction extends CreateRecord
{
    protected static string $resource = InventoryTransactionResource::class;
}
