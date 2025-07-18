<?php

namespace App\Filament\Resources\StockAdjustmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        // This would be a good place to fetch current quantity
                        // from inventory via AJAX in a real implementation
                        $set('current_quantity', 0);
                    }),
                Forms\Components\TextInput::make('current_quantity')
                    ->label('Current Qty')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->reactive(),
                Forms\Components\TextInput::make('new_quantity')
                    ->label('New Qty')
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $currentQty = (float) $get('current_quantity');
                        $newQty = (float) $state;
                        $set('quantity', $newQty - $currentQty);
                    }),
                Forms\Components\TextInput::make('quantity')
                    ->label('Adjustment (+/-)')
                    ->numeric()
                    ->disabled()
                    ->reactive(),
                Forms\Components\TextInput::make('batch_number')
                    ->maxLength(100),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(255)
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
                Tables\Columns\TextColumn::make('current_quantity')
                    ->label('Current Qty')
                    ->numeric(),
                Tables\Columns\TextColumn::make('new_quantity')
                    ->label('New Qty')
                    ->numeric(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Adjustment')
                    ->numeric()
                    ->color(fn ($record): string => $record->quantity > 0 ? 'success' : ($record->quantity < 0 ? 'danger' : 'gray')),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
