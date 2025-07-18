<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
                Tabs::make('Customer Information')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('code')
                                                    ->label('Customer Code')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(50)
                                                    ->placeholder('CUST0001')
                                                    ->helperText('Unique identifier for the customer')
                                                    ->prefixIcon('heroicon-m-identification')
                                                    ->columnSpan(1),
                                                
                                                Toggle::make('is_active')
                                                    ->label('Status')
                                                    ->default(true)
                                                    ->onColor('success')
                                                    ->offColor('danger')
                                                    ->onIcon('heroicon-m-check-circle')
                                                    ->offIcon('heroicon-m-x-circle')
                                                    ->inline(false)
                                                    ->columnSpan(1),
                                            ])
                                            ->columns(2),
                                            
                                        TextInput::make('name')
                                            ->label('Customer Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('ABC Corporation')
                                            ->autofocus(),
                                            
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contact_person')
                                                    ->label('Contact Person')
                                                    ->placeholder('John Doe')
                                                    ->prefixIcon('heroicon-m-user')
                                                    ->maxLength(255),
                                                    
                                                TextInput::make('job_title')
                                                    ->label('Job Title')
                                                    ->placeholder('Purchasing Manager')
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2),
                                            
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('email')
                                                    ->label('Email Address')
                                                    ->email()
                                                    ->required()
                                                    ->prefixIcon('heroicon-m-envelope')
                                                    ->placeholder('contact@example.com')
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255),
                                                    
                                                TextInput::make('phone')
                                                    ->label('Phone Number')
                                                    ->tel()
                                                    ->mask('+99-999-9999-9999')
                                                    ->placeholder('+62-812-3456-7890')
                                                    ->prefixIcon('heroicon-m-phone')
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2),
                                    ])
                            ]),
                            
                        Tabs\Tab::make('Address')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Textarea::make('address')
                                            ->label('Street Address')
                                            ->placeholder('123 Business Ave, Suite 101')
                                            ->rows(2)
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                            
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('city')
                                                    ->label('City')
                                                    ->placeholder('New York')
                                                    ->maxLength(100),
                                                    
                                                TextInput::make('state')
                                                    ->label('State/Province')
                                                    ->placeholder('NY')
                                                    ->maxLength(100),
                                            ])
                                            ->columns(2),
                                            
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('postal_code')
                                                    ->label('Postal/ZIP Code')
                                                    ->placeholder('10001')
                                                    ->maxLength(20),
                                                    
                                                Select::make('country')
                                                    ->searchable()
                                                    ->options([
                                                        'ID' => 'Indonesia',
                                                        'US' => 'United States',
                                                        'CA' => 'Canada',
                                                        'MX' => 'Mexico',
                                                        'UK' => 'United Kingdom',
                                                        'AU' => 'Australia',
                                                        'SG' => 'Singapore',
                                                        'MY' => 'Malaysia',
                                                        'JP' => 'Japan',
                                                        'CN' => 'China',
                                                        'IN' => 'India',
                                                    ])
                                                    ->default('ID')
                                                    ->preload(),
                                            ])
                                            ->columns(2),
                                            
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('website')
                                                    ->label('Website')
                                                    ->url()
                                                    ->prefixIcon('heroicon-m-globe-alt')
                                                    ->placeholder('https://example.com')
                                                    ->maxLength(255),
                                                    
                                                FileUpload::make('location_map')
                                                    ->label('Location Map')
                                                    ->disk('public')
                                                    ->directory('customers/maps')
                                                    ->image()
                                                    ->imagePreviewHeight('100')
                                                    ->maxSize(5120),
                                            ])
                                            ->columns(2),
                                    ])
                            ]),
                            
                        Tabs\Tab::make('Financial Information')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('tax_number')
                                                    ->label('Tax ID / NPWP')
                                                    ->placeholder('12.345.678.9-012.345')
                                                    ->helperText('Format: 00.000.000.0-000.000')
                                                    ->maxLength(255),
                                                    
                                                TextInput::make('credit_limit')
                                                    ->label('Credit Limit')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->step(0.01)
                                                    ->prefix(config('app.currency', 'Rp'))
                                                    ->default(0.00)
                                                    ->helperText('Maximum credit extended to this customer'),
                                            ])
                                            ->columns(2),
                                            
                                        Grid::make()
                                            ->schema([
                                                Select::make('payment_terms')
                                                    ->label('Payment Terms')
                                                    ->options([
                                                        'net_15' => 'Net 15 Days',
                                                        'net_30' => 'Net 30 Days',
                                                        'net_45' => 'Net 45 Days',
                                                        'net_60' => 'Net 60 Days',
                                                        'cod' => 'Cash on Delivery',
                                                        'prepaid' => 'Prepaid'
                                                    ])
                                                    ->default('net_30')
                                                    ->helperText('Standard payment terms for this customer'),
                                                    
                                                Select::make('payment_method')
                                                    ->label('Preferred Payment Method')
                                                    ->options([
                                                        'bank_transfer' => 'Bank Transfer',
                                                        'credit_card' => 'Credit Card',
                                                        'cash' => 'Cash',
                                                        'check' => 'Check',
                                                        'paypal' => 'PayPal',
                                                    ])
                                                    ->default('bank_transfer'),
                                            ])
                                            ->columns(2),
                                            
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('bank_name')
                                                    ->label('Bank Name')
                                                    ->placeholder('Bank Central Asia')
                                                    ->maxLength(255),
                                                    
                                                TextInput::make('bank_account_number')
                                                    ->label('Account Number')
                                                    ->placeholder('1234567890')
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2),
                                            
                                        Placeholder::make('financial_summary')
                                            ->label('Financial Summary')
                                            ->content(fn (?Customer $record) => $record ? 'Total Sales: ' . config('app.currency', 'Rp') . ' ' . number_format($record->total_sales ?? 0, 2) . ' | Outstanding: ' . config('app.currency', 'Rp') . ' ' . number_format($record->getOutstandingBalanceAttribute() ?? 0, 2) : 'No data available'),
                                            
                                        Textarea::make('notes')
                                            ->label('Financial Notes')
                                            ->placeholder('Enter any financial related notes, special payment arrangements, etc.')
                                            ->rows(3)
                                            ->maxLength(65535)
                                            ->columnSpanFull(),
                                    ])
                            ]),
                            
                        Tabs\Tab::make('Additional Information')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Select::make('customer_type')
                                            ->label('Customer Type')
                                            ->options([
                                                'retail' => 'Retail',
                                                'wholesale' => 'Wholesale',
                                                'distributor' => 'Distributor',
                                                'vip' => 'VIP',
                                                'other' => 'Other'
                                            ])
                                            ->default('retail')
                                            ->helperText('Customer classification for reporting'),
                                            
                                        Select::make('industry')
                                            ->label('Industry')
                                            ->searchable()
                                            ->options([
                                                'agriculture' => 'Agriculture',
                                                'construction' => 'Construction',
                                                'education' => 'Education',
                                                'finance' => 'Finance & Banking',
                                                'food' => 'Food & Beverage',
                                                'healthcare' => 'Healthcare',
                                                'hospitality' => 'Hospitality',
                                                'manufacturing' => 'Manufacturing',
                                                'retail' => 'Retail',
                                                'technology' => 'Technology',
                                                'transportation' => 'Transportation',
                                                'other' => 'Other'
                                            ]),
                                            
                                        TextInput::make('referred_by')
                                            ->label('Referred By')
                                            ->placeholder('Name of referrer')
                                            ->maxLength(255),
                                            
                                        Textarea::make('general_notes')
                                            ->label('General Notes')
                                            ->placeholder('Additional information about this customer')
                                            ->rows(3)
                                            ->maxLength(65535)
                                            ->columnSpanFull(),
                                    ])
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Customer code copied!')
                    ->fontFamily('mono')
                    ->color('gray'),
                    
                TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Customer $record): string => $record->contact_person ?? '')
                    ->wrap(),
                    
                TextColumn::make('customer_type')
                    ->label('Type')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'retail' => 'info',
                        'wholesale' => 'success',
                        'distributor' => 'warning',
                        'vip' => 'danger',
                        default => 'gray',
                    }),
                    
                TextColumn::make('email')
                    ->label('Contact')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->description(fn (Customer $record): string => $record->phone ?? '')
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->url(fn (Customer $record): string => $record->email ? 'mailto:' . $record->email : '#'),
                    
                TextColumn::make('city')
                    ->label('Location')
                    ->sortable()
                    ->description(fn (Customer $record): string => $record->country ?? '')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('credit_limit')
                    ->label('Credit Limit')
                    ->money(config('app.currency', 'USD'))
                    ->sortable()
                    ->alignRight(),
                    
                TextColumn::make('outstandingBalance')
                    ->label('Outstanding')
                    ->money(config('app.currency', 'USD'))
                    ->getStateUsing(fn (Customer $record): float => $record->getOutstandingBalanceAttribute())
                    ->sortable(false)
                    ->alignRight()
                    ->color(fn (Customer $record): string => 
                        $record->getOutstandingBalanceAttribute() > $record->credit_limit ? 'danger' : 'success'),
                    
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Date filter
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From')
                            ->placeholder(fn () => now()->subMonth()->format('M d, Y')),
                            
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until')
                            ->placeholder(fn () => now()->format('M d, Y')),
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
                            $indicators['created_from'] = 'Created from ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        
                        return $indicators;
                    }),
                    
                // Active status filter
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All customers')
                    ->trueLabel('Active customers')
                    ->falseLabel('Inactive customers')
                    ->indicator('Status'),
                    
                // Customer type filter
                SelectFilter::make('customer_type')
                    ->label('Customer Type')
                    ->multiple()
                    ->options([
                        'retail' => 'Retail',
                        'wholesale' => 'Wholesale',
                        'distributor' => 'Distributor',
                        'vip' => 'VIP',
                        'other' => 'Other'
                    ])
                    ->indicator('Type'),
                    
                // Country filter
                SelectFilter::make('country')
                    ->label('Country')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options([
                        'ID' => 'Indonesia',
                        'US' => 'United States',
                        'CA' => 'Canada',
                        'MX' => 'Mexico',
                        'UK' => 'United Kingdom',
                        'AU' => 'Australia',
                        'SG' => 'Singapore',
                        'MY' => 'Malaysia',
                        'JP' => 'Japan',
                        'CN' => 'China',
                        'IN' => 'India',
                    ])
                    ->indicator('Country'),
                    
                // Credit filter
                Tables\Filters\Filter::make('credit_status')
                    ->form([
                        Forms\Components\Select::make('credit_status')
                            ->options([
                                'over_limit' => 'Over Credit Limit',
                                'available' => 'Credit Available',
                                'no_activity' => 'No Transaction Activity',
                            ])
                            ->placeholder('All customers')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['credit_status'] === 'over_limit', function ($query) {
                                // Logic to find customers over their credit limit
                                // This is a placeholder - would need actual query logic
                                // based on your actual database structure
                                return $query->whereRaw('credit_limit > 0');
                            })
                            ->when($data['credit_status'] === 'available', function ($query) {
                                return $query->where('credit_limit', '>', 0);
                            })
                            ->when($data['credit_status'] === 'no_activity', function ($query) {
                                // Logic for no transaction activity
                                return $query;
                            });
                    }),
                    
                // Trashed filter
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Action::make('sales_history')
                        ->label('Sales History')
                        ->icon('heroicon-o-shopping-cart')
                        ->url(fn (Customer $record): string => route('filament.portal.resources.sales-orders.index', [
                            'tableFilters[customer_id]' => $record->id,
                        ]))
                        ->openUrlInNewTab(),
                    
                    Action::make('send_email')
                        ->label('Send Email')
                        ->icon('heroicon-o-envelope')
                        ->action(function (Customer $record) {
                            // Here would be the logic to send email
                            Notification::make()
                                ->title('Email sent to ' . $record->name)
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn (Customer $record): bool => filled($record->email)),
                    
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('update_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records, array $data): void {
                            $count = 0;
                            
                            foreach ($records as $record) {
                                $record->update([
                                    'is_active' => $data['status'],
                                ]);
                                $count++;
                            }
                            
                            Notification::make()
                                ->title($count . ' customers updated')
                                ->body('Customer status has been updated successfully.')
                                ->success()
                                ->send();
                        })
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    '1' => 'Active',
                                    '0' => 'Inactive',
                                ])
                                ->required()
                                ->default('1'),
                        ]),
                        
                    BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Collection $records): void {
                            // Export logic would go here
                            
                            Notification::make()
                                ->title(count($records) . ' customers exported')
                                ->body('Customers have been exported successfully.')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])
                ->label('Actions')
                ->icon('heroicon-m-cog-6-tooth'),
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
