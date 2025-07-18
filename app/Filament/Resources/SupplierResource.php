<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $navigationGroup = 'Partners';
    
    protected static ?string $recordTitleAttribute = 'name';
    
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }
    
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Code' => $record->code,
            'Email' => $record->email,
            'Phone' => $record->phone,
            'City' => $record->city,
            'Country' => $record->country,
        ];
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'email', 'phone', 'contact_person', 'city', 'address', 'tax_number'];
    }

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
                                    ->maxLength(20)
                                    ->placeholder('SUPP001')
                                    ->helperText('A unique code to identify this supplier')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->autofocus(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Company name')
                                    ->helperText('The full legal name of the supplier')
                                    ->prefixIcon('heroicon-m-building-storefront'),
                                Forms\Components\TextInput::make('contact_person')
                                    ->maxLength(100)
                                    ->placeholder('Contact person name')
                                    ->prefixIcon('heroicon-m-user'),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(100)
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('contact@supplier.com')
                                            ->prefixIcon('heroicon-m-envelope'),
                                        
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(20)
                                            ->required()
                                            ->placeholder('+6281234567890')
                                            ->prefixIcon('heroicon-m-phone'),
                                    ])
                                    ->columns(2),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active Status')
                                            ->helperText('Inactive suppliers will not appear in selection dropdowns')
                                            ->default(true),
                                        
                                        Forms\Components\Select::make('payment_terms')
                                            ->label('Payment Terms')
                                            ->options([
                                                '7' => 'Net 7',
                                                '15' => 'Net 15',
                                                '30' => 'Net 30',
                                                '60' => 'Net 60',
                                                'cod' => 'Cash on Delivery',
                                                'prepaid' => 'Prepaid',
                                            ])
                                            ->default('30')
                                            ->helperText('Standard payment terms for this supplier'),
                                    ])
                                    ->columns(2),
                            ])
                            ->columns(1),
                        
                        Forms\Components\Section::make('Additional Information')
                            ->schema([
                                Forms\Components\TextInput::make('tax_number')
                                    ->maxLength(50)
                                    ->placeholder('NPWP number or tax ID')
                                    ->helperText('Tax ID number (e.g., NPWP in Indonesia)')
                                    ->prefixIcon('heroicon-m-document-text'),
                                Forms\Components\TextInput::make('website')
                                    ->url()
                                    ->maxLength(100)
                                    ->placeholder('https://supplier-website.com')
                                    ->prefixIcon('heroicon-m-globe-alt'),
                                Forms\Components\Select::make('industry')
                                    ->options([
                                        'manufacturing' => 'Manufacturing',
                                        'wholesale' => 'Wholesale',
                                        'retail' => 'Retail',
                                        'services' => 'Services',
                                        'technology' => 'Technology',
                                        'food' => 'Food & Beverages',
                                        'construction' => 'Construction',
                                        'logistics' => 'Logistics & Transport',
                                        'other' => 'Other',
                                    ])
                                    ->searchable()
                                    ->placeholder('Select industry'),
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535)
                                    ->placeholder('Any additional notes about this supplier')
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
                                Forms\Components\Textarea::make('address')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Street address')
                                    ->rows(2),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('city')
                                            ->required()
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('state')
                                            ->maxLength(100),
                                    ])
                                    ->columns(2),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('postal_code')
                                            ->maxLength(20),
                                        Forms\Components\Select::make('country')
                                            ->searchable()
                                            ->required()
                                            ->options([
                                                'ID' => 'Indonesia',
                                                'US' => 'United States',
                                                'CA' => 'Canada',
                                                'MX' => 'Mexico',
                                                'UK' => 'United Kingdom',
                                                'AU' => 'Australia',
                                                'SG' => 'Singapore',
                                                'MY' => 'Malaysia',
                                                'CN' => 'China',
                                                'JP' => 'Japan',
                                                'TH' => 'Thailand',
                                            ])
                                            ->default('ID'),
                                    ])
                                    ->columns(2),
                            ])
                            ->columns(1),
                            
                        Forms\Components\Section::make('Bank Information')
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->placeholder('Bank name')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('bank_account_number')
                                    ->placeholder('Account number')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('bank_account_name')
                                    ->placeholder('Account name')
                                    ->maxLength(100),
                            ])
                            ->columns(1)
                            ->collapsible(),
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
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Supplier code copied!')
                    ->tooltip('Supplier unique identifier'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (Supplier $record): string => $record->name),
                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->iconColor('primary')
                    ->copyable()
                    ->copyMessage('Email copied to clipboard!')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->iconColor('success')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('country')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'ID' => 'Indonesia',
                        'US' => 'United States',
                        'CA' => 'Canada',
                        'MX' => 'Mexico',
                        'UK' => 'United Kingdom',
                        'AU' => 'Australia',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status')
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->since() // Shows as "2 hours ago", "3 days ago", etc.
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All suppliers')
                    ->trueLabel('Active suppliers')
                    ->falseLabel('Inactive suppliers'),
                
                Tables\Filters\SelectFilter::make('country')
                    ->options([
                        'ID' => 'Indonesia',
                        'US' => 'United States',
                        'CA' => 'Canada',
                        'MX' => 'Mexico',
                        'UK' => 'United Kingdom',
                        'AU' => 'Australia',
                    ])
                    ->multiple()
                    ->preload(),
                    
                Filter::make('created_at')
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        
                        return $indicators;
                    }),
                    
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Supplier deleted')
                            ->body('The supplier has been deleted successfully.'),
                    ),
                Tables\Actions\RestoreAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Supplier restored')
                            ->body('The supplier has been restored successfully.'),
                    ),
            ])
            ->headerActions([
                Tables\Actions\Action::make('import')
                    ->label('Import')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->button()
                    ->color('gray')
                    ->action(function () {
                        Notification::make()
                            ->title('Import functionality')
                            ->body(new HtmlString('This feature is coming soon. You will be able to import suppliers from Excel/CSV files.'))
                            ->info()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Suppliers deleted')
                                ->body('The selected suppliers have been deleted successfully.'),
                        ),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Mark as active')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (Supplier $supplier) {
                                $supplier->update(['is_active' => true]);
                            });
                            
                            Notification::make()
                                ->success()
                                ->title('Suppliers activated')
                                ->body(count($records) . ' suppliers have been marked as active successfully.')
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Mark as inactive')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (Supplier $supplier) {
                                $supplier->update(['is_active' => false]);
                            });
                            
                            Notification::make()
                                ->success()
                                ->title('Suppliers deactivated')
                                ->body(count($records) . ' suppliers have been marked as inactive successfully.')
                                ->send();
                        }),
                    
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PurchaseOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
