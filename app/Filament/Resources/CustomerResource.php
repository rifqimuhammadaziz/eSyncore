<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Partners';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('contact_person')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2),
                        
                        Forms\Components\Section::make('Financial Information')
                            ->schema([
                                Forms\Components\TextInput::make('tax_number')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('credit_limit')
                                    ->numeric()
                                    ->prefix(config('app.currency', '$'))
                                    ->default(0.00),
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Address')
                            ->schema([
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('city')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('state')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('postal_code')
                                    ->maxLength(255),
                                Forms\Components\Select::make('country')
                                    ->searchable()
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
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->money(config('app.currency', 'USD'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('outstandingBalance')
                    ->label('Outstanding Balance')
                    ->money(config('app.currency', 'USD'))
                    ->getStateUsing(fn (Customer $record): float => $record->getOutstandingBalanceAttribute())
                    ->sortable(false),
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All customers')
                    ->trueLabel('Active customers')
                    ->falseLabel('Inactive customers'),
                Tables\Filters\SelectFilter::make('country')
                    ->options([
                        'US' => 'United States',
                        'CA' => 'Canada',
                        'MX' => 'Mexico',
                        'UK' => 'United Kingdom',
                        'AU' => 'Australia',
                        'ID' => 'Indonesia',
                        // Add more countries as needed
                    ]),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
