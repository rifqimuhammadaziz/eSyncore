<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    
    protected static ?string $navigationGroup = 'Inventory Management';
    
    protected static ?string $navigationLabel = 'Stock Adjustments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Adjustment Information')
                            ->schema([
                                Forms\Components\TextInput::make('adjustment_number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Will be generated automatically if left empty')
                                    ->disabled(fn ($record) => $record && $record->adjustment_number),
                                Forms\Components\Select::make('warehouse_id')
                                    ->relationship('warehouse', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\DatePicker::make('adjustment_date')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\TextInput::make('reference_number')
                                    ->maxLength(255)
                                    ->helperText('External reference number if applicable'),
                                Forms\Components\Select::make('reason')
                                    ->options(StockAdjustment::getReasonOptions())
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->options(StockAdjustment::getStatusOptions())
                                    ->default('draft')
                                    ->required(),
                            ])
                            ->columns(2),
                            
                        Forms\Components\Section::make('Items')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, $set, $get) {
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
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->required(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),
                    
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Authorization')
                            ->schema([
                                Forms\Components\Select::make('created_by')
                                    ->label('Created By')
                                    ->relationship('creator', 'fullName')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('approved_by')
                                    ->label('Approved By')
                                    ->relationship('approver', 'fullName')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\DateTimePicker::make('approved_at')
                                    ->label('Approval Date/Time'),
                            ]),
                            
                        Forms\Components\Section::make('Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adjustment_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('adjustment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->formatStateUsing(fn (string $state): string => 
                        StockAdjustment::getReasonOptions()[$state] ?? $state
                    ),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('creator.fullName')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approver.fullName')
                    ->label('Approved By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(StockAdjustment::getStatusOptions()),
                Tables\Filters\SelectFilter::make('reason')
                    ->options(StockAdjustment::getReasonOptions()),
                Tables\Filters\Filter::make('adjustment_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('adjustment_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('adjustment_date', '<=', $date),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (StockAdjustment $record) => $record->status === 'pending')
                    ->action(function (StockAdjustment $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        
                        // Create inventory transactions based on the adjustment
                        $record->createInventoryTransactions();
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
            'index' => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            'edit' => Pages\EditStockAdjustment::route('/{record}/edit'),
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
