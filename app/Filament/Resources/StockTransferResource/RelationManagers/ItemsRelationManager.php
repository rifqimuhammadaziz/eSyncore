<?php

namespace App\Filament\Resources\StockTransferResource\RelationManagers;

use App\Models\Inventory;
use App\Models\StockTransferItem;
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
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get, $record) {
                        // Reset quantity if product changes
                        $set('quantity', null);
                    }),
                    
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Placeholder::make('available_quantity')
                            ->label('Available Quantity')
                            ->content(function ($get, $record) {
                                $productId = $get('product_id');
                                $transfer = $this->getOwnerRecord();
                                
                                if ($productId && $transfer && $transfer->source_warehouse_id) {
                                    $inventory = Inventory::where('product_id', $productId)
                                        ->where('warehouse_id', $transfer->source_warehouse_id)
                                        ->first();
                                    
                                    return $inventory ? number_format($inventory->quantity_available, 2) : '0.00';
                                }
                                
                                return '0.00';
                            })
                            ->hidden(fn ($get) => !$get('product_id')),
                            
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                $productId = $get('product_id');
                                $transfer = $this->getOwnerRecord();
                                
                                if ($productId && $transfer && $transfer->source_warehouse_id) {
                                    $inventory = Inventory::where('product_id', $productId)
                                        ->where('warehouse_id', $transfer->source_warehouse_id)
                                        ->first();
                                        
                                    if ($inventory && $state > $inventory->quantity_available) {
                                        $set('quantity', $inventory->quantity_available);
                                    }
                                }
                            }),
                    ])
                    ->columns(2),
                    
                Forms\Components\TextInput::make('batch_number')
                    ->maxLength(50),
                    
                Forms\Components\DatePicker::make('expiry_date'),
                
                Forms\Components\Textarea::make('notes')
                    ->rows(2)
                    ->columnSpan('full'),
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
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric(2)
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->disabled(fn () => $this->getOwnerRecord()->status === 'approved' || $this->getOwnerRecord()->status === 'completed'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(fn () => $this->getOwnerRecord()->status === 'approved' || $this->getOwnerRecord()->status === 'completed'),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn () => $this->getOwnerRecord()->status === 'approved' || $this->getOwnerRecord()->status === 'completed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->disabled(fn () => $this->getOwnerRecord()->status === 'approved' || $this->getOwnerRecord()->status === 'completed'),
                ]),
            ]);
    }
}
