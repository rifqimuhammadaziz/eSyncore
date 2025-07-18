<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockItemsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected static ?string $heading = 'Low Stock Items';
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Inventory::query()
                    ->whereRaw('quantity_available <= minimum_stock')
                    ->with(['product', 'warehouse'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_available')
                    ->label('Available')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Minimum Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_point')
                    ->label('Reorder Point')
                    ->sortable(),
                Tables\Columns\IconColumn::make('needs_reorder')
                    ->label('Needs Reorder')
                    ->boolean()
                    ->getStateUsing(fn (Inventory $record): bool => $record->quantity_available <= $record->reorder_point),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse'),
            ]);
    }
}
