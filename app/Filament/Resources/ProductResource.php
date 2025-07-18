<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('code')
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                                Forms\Components\TextInput::make('barcode')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('unit')
                                    ->maxLength(255)
                                    ->placeholder('e.g., pcs, kg, liter'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2),
                            
                        Forms\Components\Section::make('Pricing')
                            ->schema([
                                Forms\Components\TextInput::make('purchase_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix(config('app.currency', '$')),
                                Forms\Components\TextInput::make('selling_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix(config('app.currency', '$')),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),
                    
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Media')
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Product Image')
                                    ->image()
                                    ->directory('products')
                                    ->visibility('public'),
                            ]),
                        
                        Forms\Components\Section::make('Additional Attributes')
                            ->schema([
                                Forms\Components\KeyValue::make('attributes')
                                    ->keyLabel('Attribute')
                                    ->valueLabel('Value')
                                    ->reorderable()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
                    
                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular(false)
                    ->square(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->money(config('app.currency', 'USD'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money(config('app.currency', 'USD'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalQuantity')
                    ->label('Available Stock')
                    ->getStateUsing(fn (Product $record): float => $record->getTotalQuantityAttribute())
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All products')
                    ->trueLabel('Active products')
                    ->falseLabel('Inactive products'),
                Tables\Filters\TrashedFilter::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
