<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\RelationManagers;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Sales Order Information')
                            ->schema([
                                Forms\Components\TextInput::make('so_number')
                                    ->label('SO Number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('Will be generated automatically if left empty')
                                    ->disabled(fn ($record) => $record && $record->so_number),
                                Forms\Components\Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable(),
                                Forms\Components\DatePicker::make('order_date')
                                    ->label('Order Date')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\DatePicker::make('expected_shipping_date')
                                    ->label('Expected Shipping Date')
                                    ->required(),
                                Forms\Components\DatePicker::make('shipping_date')
                                    ->label('Actual Shipping Date'),
                                Forms\Components\Select::make('status')
                                    ->options(SalesOrder::getStatusOptions())
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
                                            ->options(SalesOrderItem::getStatusOptions())
                                            ->default('pending'),
                                        Forms\Components\TextInput::make('shipped_quantity')
                                            ->label('Shipped')
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
                                    ->options(SalesOrder::getPaymentStatusOptions())
                                    ->default('unpaid')
                                    ->required(),
                                Forms\Components\DatePicker::make('payment_due_date')
                                    ->label('Payment Due Date'),
                                Forms\Components\TextInput::make('payment_terms')
                                    ->label('Payment Terms')
                                    ->placeholder('e.g., Net 30')
                                    ->maxLength(255),
                            ]),
                            
                        Forms\Components\Section::make('Shipping Information')
                            ->schema([
                                Forms\Components\Select::make('shipping_method')
                                    ->label('Shipping Method')
                                    ->options(SalesOrder::getShippingMethodOptions())
                                    ->required(),
                                Forms\Components\TextInput::make('tracking_number')
                                    ->label('Tracking Number')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('shipping_address')
                                    ->label('Shipping Address')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('shipping_city')
                                    ->label('City')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('shipping_state')
                                    ->label('State/Province')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('shipping_postal_code')
                                    ->label('Postal Code')
                                    ->maxLength(255),
                                Forms\Components\Select::make('shipping_country')
                                    ->label('Country')
                                    ->options([
                                        'US' => 'United States',
                                        'CA' => 'Canada',
                                        'MX' => 'Mexico',
                                        'UK' => 'United Kingdom',
                                        'AU' => 'Australia',
                                        'ID' => 'Indonesia',
                                        // Add more countries as needed
                                    ]),
                            ])
                            ->columns(2),
                            
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
                Tables\Columns\TextColumn::make('so_number')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_shipping_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'processing' => 'info',
                        'shipped_partial' => 'info',
                        'shipped_complete' => 'success',
                        'delivered' => 'success',
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
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(SalesOrder::getStatusOptions()),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(SalesOrder::getPaymentStatusOptions()),
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
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
