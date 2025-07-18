<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryTransactionResource\Pages;
use App\Models\InventoryTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryTransactionResource extends Resource
{
    protected static ?string $model = InventoryTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    
    protected static ?string $navigationGroup = 'Inventory Management';
    
    protected static ?string $navigationLabel = 'Stock Transactions';

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
                Forms\Components\Select::make('transaction_type')
                    ->options(InventoryTransaction::getTransactionTypeOptions())
                    ->required(),
                Forms\Components\Select::make('reference_type')
                    ->options(InventoryTransaction::getReferenceTypeOptions())
                    ->default('manual'),
                Forms\Components\TextInput::make('reference_id')
                    ->numeric()
                    ->label('Reference ID (if applicable)'),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('batch_number')
                    ->maxLength(100),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\Select::make('created_by')
                    ->relationship('creator', 'employee_id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'stock_in', 'adjustment_add', 'transfer_in', 'purchase', 'return_in' => 'success',
                        'stock_out', 'adjustment_remove', 'transfer_out', 'sales', 'return_out' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => 
                        InventoryTransaction::getTransactionTypeOptions()[$state] ?? $state
                    ),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->formatStateUsing(fn (string $state): string => 
                        InventoryTransaction::getReferenceTypeOptions()[$state] ?? $state
                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_id')
                    ->label('Ref #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('creator.fullName')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options(InventoryTransaction::getTransactionTypeOptions()),
                Tables\Filters\SelectFilter::make('reference_type')
                    ->options(InventoryTransaction::getReferenceTypeOptions()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListInventoryTransactions::route('/'),
            'create' => Pages\CreateInventoryTransaction::route('/create'),
            'view' => Pages\ViewInventoryTransaction::route('/{record}'),
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
