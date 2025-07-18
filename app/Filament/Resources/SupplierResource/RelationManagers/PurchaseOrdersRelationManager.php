<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\PurchaseOrder;
use App\Models\Company;
use Carbon\Carbon;

class PurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrders';

    protected static ?string $recordTitleAttribute = 'po_number';
    
    protected static ?string $title = 'Purchase Orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('po_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        // For currency formatting
        $company = Company::first();
        $currencySymbol = $company ? $company->currency_symbol : 'Rp';
        $thousandSeparator = $company ? $company->thousand_separator : '.';
        $decimalSeparator = $company ? $company->decimal_separator : ',';
        $decimalPrecision = $company ? $company->decimal_precision : 0;
        
        $formatCurrency = function ($amount) use ($currencySymbol, $decimalPrecision, $thousandSeparator, $decimalSeparator) {
            $formatted = number_format($amount, $decimalPrecision, $decimalSeparator, $thousandSeparator);
            return $currencySymbol . ' ' . $formatted;
        };

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('po_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'received' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => $formatCurrency($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'received' => 'Received',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\Filter::make('po_date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('until_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('po_date', '>=', $date),
                            )
                            ->when(
                                $data['until_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('po_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['from_date'] ?? null) {
                            $indicators['from_date'] = 'From ' . Carbon::parse($data['from_date'])->toFormattedDateString();
                        }
                        
                        if ($data['until_date'] ?? null) {
                            $indicators['until_date'] = 'Until ' . Carbon::parse($data['until_date'])->toFormattedDateString();
                        }
                        
                        return $indicators;
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->url(fn (): string => route('filament.portal.resources.purchase-orders.create', [
                        'supplier_id' => $this->getOwnerRecord()->id,
                    ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (PurchaseOrder $record): string => route('filament.portal.resources.purchase-orders.edit', $record)),
                Tables\Actions\EditAction::make()
                    ->url(fn (PurchaseOrder $record): string => route('filament.portal.resources.purchase-orders.edit', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No delete action as purchase orders are typically not deleted
                ]),
            ])
            ->defaultSort('po_date', 'desc');
    }
}
