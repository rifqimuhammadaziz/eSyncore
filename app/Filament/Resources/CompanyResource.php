<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationGroup = 'System';
    
    protected static ?string $navigationLabel = 'Company Settings';
    
    protected static ?int $navigationSort = 100;
    
    protected static ?string $recordTitleAttribute = 'name';
    
    // Hide pluralized model name in breadcrumbs
    protected static bool $shouldRegisterNavigation = true;
    
    // No need for navigation badge as this is a singleton resource
    public static function getNavigationBadge(): ?string
    {
        return null;
    }
    
    // Always redirect to the edit page of the first company record
    public static function getNavigationUrl(): string
    {
        $company = static::getModel()::first();
        
        return $company
            ? static::getUrl('edit', ['record' => $company])
            : static::getUrl('create');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Company Settings')
                    ->tabs([
                        // GENERAL INFORMATION TAB
                        Tab::make('General Information')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Company Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('ABC Corporation')
                                                    ->helperText('Your company name as it will appear on documents')
                                                    ->autofocus(),
                                                    
                                                TextInput::make('legal_name')
                                                    ->label('Legal Name')
                                                    ->maxLength(255)
                                                    ->placeholder('ABC Corporation Ltd.')
                                                    ->helperText('Full legal name if different from company name'),
                                            ])
                                            ->columns(2),
                                        
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('tax_id')
                                                    ->label('Tax ID')
                                                    ->maxLength(50)
                                                    ->placeholder('123456789')
                                                    ->helperText('Government-issued tax identification number'),
                                                    
                                                TextInput::make('registration_number')
                                                    ->label('Registration Number')
                                                    ->maxLength(50)
                                                    ->placeholder('REG12345')
                                                    ->helperText('Business registration or incorporation number'),
                                                    
                                                Select::make('industry')
                                                    ->label('Industry')
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
                                                        'other' => 'Other',
                                                    ])
                                                    ->searchable(),
                                            ])
                                            ->columns(3),
                                        
                                        Textarea::make('description')
                                            ->label('Description')
                                            ->maxLength(1000)
                                            ->columnSpanFull()
                                            ->placeholder('Brief description of your company'),
                                        
                                        Grid::make()
                                            ->schema([
                                                FileUpload::make('logo')
                                                    ->label('Company Logo')
                                                    ->image()
                                                    ->imageResizeMode('cover')
                                                    ->imageCropAspectRatio('16:9')
                                                    ->imageResizeTargetWidth('1920')
                                                    ->imageResizeTargetHeight('1080')
                                                    ->directory('companies/logos')
                                                    ->helperText('Recommended: 1920x1080px, JPG or PNG format')
                                                    ->columnSpanFull(),
                                                
                                                FileUpload::make('favicon')
                                                    ->label('Favicon')
                                                    ->image()
                                                    ->imageResizeMode('cover')
                                                    ->imageResizeTargetWidth('64')
                                                    ->imageResizeTargetHeight('64')
                                                    ->directory('companies/favicons')
                                                    ->helperText('Recommended: 64x64px, PNG or ICO format')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2),
                                    ]),
                            ]),
                            
                        // CONTACT & ADDRESS TAB
                        Tab::make('Contact & Address')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Contact Information')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->maxLength(255)
                                                    ->placeholder('contact@company.com')
                                                    ->prefixIcon('heroicon-m-envelope'),
                                                
                                                TextInput::make('phone')
                                                    ->label('Phone')
                                                    ->tel()
                                                    ->maxLength(50)
                                                    ->placeholder('+1234567890')
                                                    ->prefixIcon('heroicon-m-phone'),
                                                
                                                TextInput::make('fax')
                                                    ->label('Fax')
                                                    ->tel()
                                                    ->maxLength(50)
                                                    ->placeholder('+1234567890')
                                                    ->prefixIcon('heroicon-m-document'),
                                                
                                                TextInput::make('website')
                                                    ->label('Website')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->placeholder('https://www.company.com')
                                                    ->suffixIcon('heroicon-m-globe-alt'),
                                            ])
                                            ->columns(2),
                                    ]),
                                
                                Section::make('Company Address')
                                    ->schema([
                                        Textarea::make('address')
                                            ->label('Street Address')
                                            ->maxLength(255)
                                            ->placeholder('123 Business Ave, Suite 100')
                                            ->columnSpanFull(),
                                        
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('city')
                                                    ->label('City')
                                                    ->maxLength(100)
                                                    ->placeholder('New York'),
                                                
                                                TextInput::make('state')
                                                    ->label('State/Province')
                                                    ->maxLength(100)
                                                    ->placeholder('NY'),
                                                
                                                TextInput::make('postal_code')
                                                    ->label('Postal Code')
                                                    ->maxLength(20)
                                                    ->placeholder('10001'),
                                                
                                                Select::make('country')
                                                    ->label('Country')
                                                    ->searchable()
                                                    ->options([
                                                        'ID' => 'Indonesia',
                                                        'US' => 'United States',
                                                        'CA' => 'Canada',
                                                        'GB' => 'United Kingdom',
                                                        'AU' => 'Australia',
                                                        'SG' => 'Singapore',
                                                        'MY' => 'Malaysia',
                                                        // Add more as needed
                                                    ])
                                                    ->default('ID'),
                                            ])
                                            ->columns(2),
                                        
                                        TextInput::make('google_maps_url')
                                            ->label('Google Maps URL')
                                            ->url()
                                            ->maxLength(1000)
                                            ->placeholder('https://goo.gl/maps/...')
                                            ->helperText('Link to your location on Google Maps')
                                            ->columnSpanFull(),
                                    ]),
                                    
                                Section::make('Contact Person')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('contact_person_name')
                                                    ->label('Name')
                                                    ->maxLength(255)
                                                    ->placeholder('John Doe'),
                                                
                                                TextInput::make('contact_person_position')
                                                    ->label('Position')
                                                    ->maxLength(255)
                                                    ->placeholder('CEO'),
                                                
                                                TextInput::make('contact_person_email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->maxLength(255)
                                                    ->placeholder('john@company.com'),
                                                
                                                TextInput::make('contact_person_phone')
                                                    ->label('Phone')
                                                    ->tel()
                                                    ->maxLength(50)
                                                    ->placeholder('+1234567890'),
                                            ])
                                            ->columns(2),
                                    ]),
                            ]),
                            
                        // LOCALE & CURRENCY TAB
                        Tab::make('Locale & Currency')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Locale Settings')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                Select::make('language')
                                                    ->label('Language')
                                                    ->options([
                                                        'en' => 'English',
                                                        'id' => 'Bahasa Indonesia',
                                                        'es' => 'Spanish',
                                                        'fr' => 'French',
                                                        'de' => 'German',
                                                        'zh' => 'Chinese',
                                                        'ja' => 'Japanese',
                                                        // Add more as needed
                                                    ])
                                                    ->default('en')
                                                    ->searchable(),
                                                
                                                Select::make('timezone')
                                                    ->label('Timezone')
                                                    ->options([
                                                        'UTC' => 'UTC',
                                                        'Asia/Jakarta' => 'Asia/Jakarta (GMT+7)',
                                                        'America/New_York' => 'America/New York (GMT-5)',
                                                        'Europe/London' => 'Europe/London (GMT+0)',
                                                        'Asia/Tokyo' => 'Asia/Tokyo (GMT+9)',
                                                        // Add more as needed
                                                    ])
                                                    ->default('UTC')
                                                    ->searchable(),
                                            ])
                                            ->columns(2),
                                        
                                        Grid::make()
                                            ->schema([
                                                Select::make('date_format')
                                                    ->label('Date Format')
                                                    ->options([
                                                        'Y-m-d' => 'YYYY-MM-DD (2023-12-31)',
                                                        'd/m/Y' => 'DD/MM/YYYY (31/12/2023)',
                                                        'm/d/Y' => 'MM/DD/YYYY (12/31/2023)',
                                                        'd-m-Y' => 'DD-MM-YYYY (31-12-2023)',
                                                        'd M Y' => 'DD MMM YYYY (31 Dec 2023)',
                                                        'F j, Y' => 'Month D, YYYY (December 31, 2023)',
                                                    ])
                                                    ->default('Y-m-d'),
                                                
                                                Select::make('time_format')
                                                    ->label('Time Format')
                                                    ->options([
                                                        'H:i' => '24-hour (14:30)',
                                                        'h:i A' => '12-hour (2:30 PM)',
                                                        'H:i:s' => '24-hour with seconds (14:30:00)',
                                                        'h:i:s A' => '12-hour with seconds (2:30:00 PM)',
                                                    ])
                                                    ->default('H:i'),
                                            ])
                                            ->columns(2),
                                    ]),
                                
                                Section::make('Currency Settings')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('currency_code')
                                                    ->label('Currency Code')
                                                    ->maxLength(3)
                                                    ->placeholder('USD')
                                                    ->default('USD'),
                                                
                                                TextInput::make('currency_symbol')
                                                    ->label('Currency Symbol')
                                                    ->maxLength(10)
                                                    ->placeholder('$')
                                                    ->default('$'),
                                                
                                                Select::make('currency_position')
                                                    ->label('Symbol Position')
                                                    ->options([
                                                        'before' => 'Before amount ($100)',
                                                        'after' => 'After amount (100$)',
                                                    ])
                                                    ->default('before'),
                                            ])
                                            ->columns(3),
                                        
                                        Grid::make()
                                            ->schema([
                                                Select::make('decimal_precision')
                                                    ->label('Decimal Precision')
                                                    ->options([
                                                        0 => 'No decimals (100)',
                                                        1 => 'One decimal (100.0)',
                                                        2 => 'Two decimals (100.00)',
                                                        3 => 'Three decimals (100.000)',
                                                        4 => 'Four decimals (100.0000)',
                                                    ])
                                                    ->default(2),
                                                
                                                TextInput::make('thousand_separator')
                                                    ->label('Thousand Separator')
                                                    ->maxLength(1)
                                                    ->placeholder(',')
                                                    ->default(','),
                                                
                                                TextInput::make('decimal_separator')
                                                    ->label('Decimal Separator')
                                                    ->maxLength(1)
                                                    ->placeholder('.')
                                                    ->default('.'),
                                            ])
                                            ->columns(3),
                                    ]),
                                
                                Section::make('Business Settings')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('fiscal_year_start')
                                                    ->label('Fiscal Year Start (MM-DD)')
                                                    ->maxLength(5)
                                                    ->placeholder('01-01')
                                                    ->default('01-01')
                                                    ->helperText('Format: MM-DD (e.g., 01-01 for January 1st)'),
                                                
                                                Select::make('accounting_method')
                                                    ->label('Accounting Method')
                                                    ->options([
                                                        'accrual' => 'Accrual Basis',
                                                        'cash' => 'Cash Basis',
                                                    ])
                                                    ->default('accrual'),
                                                
                                                Select::make('default_payment_terms')
                                                    ->label('Default Payment Terms')
                                                    ->options([
                                                        'due_receipt' => 'Due on Receipt',
                                                        'net_15' => 'Net 15 Days',
                                                        'net_30' => 'Net 30 Days',
                                                        'net_45' => 'Net 45 Days',
                                                        'net_60' => 'Net 60 Days',
                                                        'custom' => 'Custom',
                                                    ])
                                                    ->default('net_30'),
                                            ])
                                            ->columns(3),
                                        
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('invoice_due_days')
                                                    ->label('Default Invoice Due (Days)')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->maxValue(365)
                                                    ->default(30)
                                                    ->helperText('Number of days before invoices are due'),
                                                
                                                TextInput::make('quote_valid_days')
                                                    ->label('Quote Validity (Days)')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->maxValue(365)
                                                    ->default(30)
                                                    ->helperText('Number of days quotes remain valid'),
                                            ])
                                            ->columns(2),
                                    ]),
                            ]),
                            
                        // APPEARANCE TAB
                        Tab::make('Appearance')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make('Theme Settings')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                ColorPicker::make('primary_color')
                                                    ->label('Primary Color')
                                                    ->default('#4338ca'),
                                                
                                                ColorPicker::make('secondary_color')
                                                    ->label('Secondary Color')
                                                    ->default('#f59e0b'),
                                                
                                                Select::make('theme_mode')
                                                    ->label('Default Theme Mode')
                                                    ->options([
                                                        'light' => 'Light Mode',
                                                        'dark' => 'Dark Mode',
                                                        'system' => 'System Default',
                                                    ])
                                                    ->default('light'),
                                            ])
                                            ->columns(3),
                                        
                                        Toggle::make('enable_notifications')
                                            ->label('Enable System Notifications')
                                            ->helperText('Enable or disable in-app notifications')
                                            ->default(true)
                                            ->columnSpanFull(),
                                        
                                        Placeholder::make('theme_preview')
                                            ->label('Theme Preview')
                                            ->content('Theme preview will be implemented in a future update.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        
                        // SOCIAL MEDIA TAB
                        Tab::make('Social Media')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make('Social Media Links')
                                    ->schema([
                                        Grid::make()
                                            ->schema([
                                                TextInput::make('social_media.facebook')
                                                    ->label('Facebook')
                                                    ->url()
                                                    ->prefixIcon('heroicon-m-globe-alt')
                                                    ->placeholder('https://facebook.com/yourcompany'),
                                                
                                                TextInput::make('social_media.twitter')
                                                    ->label('Twitter / X')
                                                    ->url()
                                                    ->prefixIcon('heroicon-m-globe-alt')
                                                    ->placeholder('https://twitter.com/yourcompany'),
                                                
                                                TextInput::make('social_media.instagram')
                                                    ->label('Instagram')
                                                    ->url()
                                                    ->prefixIcon('heroicon-m-globe-alt')
                                                    ->placeholder('https://instagram.com/yourcompany'),
                                                
                                                TextInput::make('social_media.linkedin')
                                                    ->label('LinkedIn')
                                                    ->url()
                                                    ->prefixIcon('heroicon-m-globe-alt')
                                                    ->placeholder('https://linkedin.com/company/yourcompany'),
                                                
                                                TextInput::make('social_media.youtube')
                                                    ->label('YouTube')
                                                    ->url()
                                                    ->prefixIcon('heroicon-m-globe-alt')
                                                    ->placeholder('https://youtube.com/c/yourcompany'),
                                                
                                                TextInput::make('social_media.tiktok')
                                                    ->label('TikTok')
                                                    ->url()
                                                    ->prefixIcon('heroicon-m-globe-alt')
                                                    ->placeholder('https://tiktok.com/@yourcompany'),
                                            ])
                                            ->columns(2),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->searchable(),
                
                TextColumn::make('phone')
                    ->label('Phone')
                    ->icon('heroicon-m-phone')
                    ->searchable(),
                
                TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),
                
                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),
                
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
