<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
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
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('quantity_reserved')
                    ->label('Reserved')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_on_hand')
                    ->label('On Hand')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bin_location')
                    ->label('Location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('needs_reorder')
                    ->label('Needs Reorder')
                    ->boolean(),
                Tables\Columns\IconColumn::make('low_stock')
                    ->label('Low Stock')
                    ->boolean(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse')
                    ->searchable(),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quantity_available <= minimum_stock')),
                Tables\Filters\Filter::make('needs_reorder')
                    ->label('Needs Reorder')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quantity_available <= reorder_point')),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }    
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
