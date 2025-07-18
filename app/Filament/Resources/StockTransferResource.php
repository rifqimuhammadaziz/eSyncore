<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransferResource\Pages;
use App\Filament\Resources\StockTransferResource\RelationManagers;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    
    protected static ?string $navigationGroup = 'Inventory Management';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Transfer Information')
                            ->schema([
                                Forms\Components\TextInput::make('transfer_number')
                                    ->default(fn () => StockTransfer::generateTransferNumber())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),
                                    
                                Forms\Components\DatePicker::make('transfer_date')
                                    ->required()
                                    ->default(now()),
                                    
                                Forms\Components\Select::make('source_warehouse_id')
                                    ->label('Source Warehouse')
                                    ->relationship('sourceWarehouse', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Prevent selecting the same warehouse as source and destination
                                        $set('destination_warehouse_id', null);
                                    }),
                                    
                                Forms\Components\Select::make('destination_warehouse_id')
                                    ->label('Destination Warehouse')
                                    ->relationship('destinationWarehouse', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->options(function (callable $get) {
                                        $sourceId = $get('source_warehouse_id');
                                        if (!$sourceId) return Warehouse::pluck('name', 'id');
                                        
                                        return Warehouse::where('id', '!=', $sourceId)
                                            ->pluck('name', 'id');
                                    }),
                                    
                                Forms\Components\Select::make('status')
                                    ->options(StockTransfer::getStatuses())
                                    ->default(StockTransfer::STATUS_DRAFT)
                                    ->disabled(fn (string $context): bool => $context === 'edit' && auth()->user()->cannot('approve', StockTransfer::class))
                                    ->required(),
                                    
                                Forms\Components\Textarea::make('notes')
                                    ->rows(3)
                                    ->columnSpan('full'),
                            ])
                            ->columns(2),
                            
                        Forms\Components\Section::make('Transfer Items')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
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
                                            
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0.01)
                                            ->step(0.01)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                                                $productId = $get('product_id');
                                                $transferId = $livewire->getOwnerRecord()->id ?? null;
                                                
                                                if ($productId && $transferId) {
                                                    $transfer = StockTransfer::find($transferId);
                                                    if ($transfer) {
                                                        $sourceWarehouseId = $transfer->source_warehouse_id;
                                                        $inventory = \App\Models\Inventory::where('product_id', $productId)
                                                            ->where('warehouse_id', $sourceWarehouseId)
                                                            ->first();
                                                            
                                                        if ($inventory && $state > $inventory->quantity_available) {
                                                            $set('quantity', $inventory->quantity_available);
                                                        }
                                                    }
                                                }
                                            }),
                                            
                                        Forms\Components\TextInput::make('batch_number')
                                            ->maxLength(50),
                                            
                                        Forms\Components\DatePicker::make('expiry_date'),
                                        
                                        Forms\Components\Textarea::make('notes')
                                            ->rows(2)
                                            ->columnSpan('full'),
                                    ])
                                    ->defaultItems(0)
                                    ->reorderable(false)
                                    ->columns(2)
                                    ->columnSpan('full')
                                    ->required(),
                            ])
                    ])
                    ->columnSpan(['lg' => 2]),
                    
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Authorization')
                            ->schema([
                                Forms\Components\Placeholder::make('created_by')
                                    ->label('Created By')
                                    ->content(fn (StockTransfer $record): string => $record->creator?->fullName ?? 'Not specified'),
                                    
                                Forms\Components\Placeholder::make('approved_by')
                                    ->label('Approved By')
                                    ->content(fn (StockTransfer $record): string => $record->approver?->fullName ?? 'Not approved yet'),
                                    
                                Forms\Components\Placeholder::make('approved_at')
                                    ->label('Approved At')
                                    ->content(fn (StockTransfer $record): string => $record->approved_at?->format('M d, Y H:i') ?? 'Not approved yet'),
                            ])
                            ->hidden(fn ($record) => $record === null)
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transfer_number')
                    ->label('Transfer #')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sourceWarehouse.name')
                    ->label('From')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('destinationWarehouse.name')
                    ->label('To')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('transfer_date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source_warehouse_id')
                    ->label('Source Warehouse')
                    ->relationship('sourceWarehouse', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('destination_warehouse_id')
                    ->label('Destination Warehouse')
                    ->relationship('destinationWarehouse', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->options(StockTransfer::getStatuses()),
                    
                Tables\Filters\TrashedFilter::make(),
                
                Tables\Filters\Filter::make('transfer_date')
                    ->form([
                        Forms\Components\DatePicker::make('transfer_from'),
                        Forms\Components\DatePicker::make('transfer_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['transfer_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transfer_date', '>=', $date),
                            )
                            ->when(
                                $data['transfer_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transfer_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve & Process')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (StockTransfer $record): bool => 
                        $record->status === StockTransfer::STATUS_PENDING || 
                        $record->status === StockTransfer::STATUS_DRAFT)
                    ->action(function (StockTransfer $record) {
                        $record->status = StockTransfer::STATUS_APPROVED;
                        $record->approved_by = auth()->id();
                        $record->approved_at = now();
                        $record->save();
                        
                        // Process the transfer
                        $record->processTransfer();
                    }),
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransfers::route('/'),
            'create' => Pages\CreateStockTransfer::route('/create'),
            'edit' => Pages\EditStockTransfer::route('/{record}/edit'),
            'view' => Pages\ViewStockTransfer::route('/{record}'),
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
