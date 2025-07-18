<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Human Resources';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('employee_id')
                                    ->label('Employee ID')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2),
                            
                        Forms\Components\Section::make('Employment Details')
                            ->schema([
                                Forms\Components\TextInput::make('position')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('department')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('hire_date')
                                    ->required(),
                                Forms\Components\DatePicker::make('termination_date'),
                                Forms\Components\TextInput::make('salary')
                                    ->numeric()
                                    ->prefix(config('app.currency', '$'))
                                    ->required(),
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(2),
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
                            ->collapsible(),
                            
                        Forms\Components\Section::make('Banking Information')
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('bank_account')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('tax_id')
                                    ->maxLength(255),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 1]),
                    
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_id')
                    ->label('Employee ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fullName')
                    ->label('Name')
                    ->searchable([
                        'first_name',
                        'last_name',
                    ])
                    ->sortable([
                        'first_name',
                        'last_name',
                    ]),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('salary')
                    ->money(config('app.currency', 'USD'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Account')
                    ->toggleable(),
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
                Tables\Filters\SelectFilter::make('department')
                    ->options(function() {
                        return Employee::distinct()->pluck('department', 'department')->toArray();
                    }),
                Tables\Filters\SelectFilter::make('position')
                    ->options(function() {
                        return Employee::distinct()->pluck('position', 'position')->toArray();
                    }),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All employees')
                    ->trueLabel('Active employees')
                    ->falseLabel('Inactive employees'),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
