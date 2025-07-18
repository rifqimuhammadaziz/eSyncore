<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use App\Models\Inventory;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('quantity_available')
                    ->label('Available Quantity')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\TextInput::make('quantity_reserved')
                    ->label('Reserved Quantity')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\TextInput::make('minimum_stock')
                    ->label('Minimum Stock')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\TextInput::make('reorder_point')
                    ->label('Reorder Point')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\TextInput::make('bin_location')
                    ->label('Bin Location')
                    ->maxLength(50),
                Forms\Components\DatePicker::make('last_counted_date')
                    ->label('Last Counted Date'),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Expiry Date'),
                Forms\Components\TextInput::make('batch_number')
                    ->label('Batch Number')
                    ->maxLength(100),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_available')
                    ->label('Available')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_reserved')
                    ->label('Reserved')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('On Hand')
                    ->getStateUsing(fn (Inventory $record): float => $record->quantity_available - $record->quantity_reserved)
                    ->sortable(),
                Tables\Columns\TextColumn::make('bin_location')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\IconColumn::make('needs_reorder')
                    ->label('Needs Reorder')
                    ->boolean()
                    ->getStateUsing(fn (Inventory $record): bool => $record->quantity_available <= $record->reorder_point)
                    ->sortable(),
                Tables\Columns\IconColumn::make('low_stock')
                    ->label('Low Stock')
                    ->boolean()
                    ->getStateUsing(fn (Inventory $record): bool => $record->quantity_available <= $record->minimum_stock)
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_counted_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quantity_available <= minimum_stock')),
                Tables\Filters\Filter::make('needs_reorder')
                    ->label('Needs Reorder')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quantity_available <= reorder_point')),
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
    
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
