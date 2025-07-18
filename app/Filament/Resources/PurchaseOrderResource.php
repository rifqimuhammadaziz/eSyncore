<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationGroup = 'Purchasing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Purchase Order Information')
                            ->schema([
                                Forms\Components\TextInput::make('po_number')
                                    ->label('PO Number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Will be generated automatically if left empty')
                                    ->disabled(fn ($record) => $record && $record->po_number),
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\DatePicker::make('order_date')
                                    ->label('Order Date')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\DatePicker::make('expected_delivery_date')
                                    ->label('Expected Delivery Date')
                                    ->required(),
                                Forms\Components\DatePicker::make('delivery_date')
                                    ->label('Actual Delivery Date'),
                                Forms\Components\Select::make('status')
                                    ->options(PurchaseOrder::getStatusOptions())
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
                                            ->label('Product')
                                            ->relationship('product', 'name')
                                            ->required()
                                            ->reactive()
                                            ->preload()
                                            ->searchable(),
                                        Forms\Components\TextInput::make('description')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(0.01),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->numeric()
                                            ->required()
                                            ->prefix(config('app.currency', '$'))
                                            ->minValue(0.01),
                                        Forms\Components\TextInput::make('tax_percentage')
                                            ->label('Tax %')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('%'),
                                        Forms\Components\TextInput::make('discount_percentage')
                                            ->label('Discount %')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('%'),
                                        Forms\Components\Select::make('status')
                                            ->options(PurchaseOrderItem::getStatusOptions())
                                            ->default('pending'),
                                        Forms\Components\TextInput::make('received_quantity')
                                            ->label('Received')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                    ])
                                    ->columns(4)
                                    ->defaultItems(1)
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),
                    
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Summary')
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix(config('app.currency', '$'))
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('tax')
                                    ->numeric()
                                    ->prefix(config('app.currency', '$'))
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('discount')
                                    ->numeric()
                                    ->prefix(config('app.currency', '$'))
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Shipping Cost')
                                    ->numeric()
                                    ->prefix(config('app.currency', '$'))
                                    ->default(0),
                                Forms\Components\TextInput::make('grand_total')
                                    ->label('Grand Total')
                                    ->numeric()
                                    ->prefix(config('app.currency', '$'))
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                                    
                        Forms\Components\Section::make('Payment Information')
                            ->schema([
                                Forms\Components\Select::make('payment_status')
                                    ->label('Payment Status')
                                    ->options(PurchaseOrder::getPaymentStatusOptions())
                                    ->default('unpaid')
                                    ->required(),
                                Forms\Components\DatePicker::make('payment_due_date')
                                    ->label('Payment Due Date'),
                                Forms\Components\TextInput::make('payment_terms')
                                    ->label('Payment Terms')
                                    ->placeholder('e.g., Net 30')
                                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'ordered' => 'info',
                        'received_partial' => 'info',
                        'received_complete' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money(config('app.currency', 'USD'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.fullName')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approver.fullName')
                    ->label('Approved By')
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
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(PurchaseOrder::getStatusOptions()),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(PurchaseOrder::getPaymentStatusOptions()),
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('order_date')
                    ->form([
                        Forms\Components\DatePicker::make('order_date_from'),
                        Forms\Components\DatePicker::make('order_date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['order_date_from'] ?? null) {
                            $indicators['order_date_from'] = 'Order from ' . Carbon::parse($data['order_date_from'])->toFormattedDateString();
                        }
                        
                        if ($data['order_date_until'] ?? null) {
                            $indicators['order_date_until'] = 'Order until ' . Carbon::parse($data['order_date_until'])->toFormattedDateString();
                        }
                        
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'receive' => Pages\ReceivePurchaseOrder::route('/{record}/receive'),
        ];
    }
}
