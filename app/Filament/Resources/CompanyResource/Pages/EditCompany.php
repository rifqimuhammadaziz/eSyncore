<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;
    
    /**
     * Custom page title to reflect company settings management
     */
    public function getTitle(): string|Htmlable
    {
        return __('Company Settings');
    }

    protected function getHeaderActions(): array
    {
        // We don't want to allow deletion of the only company record
        // Instead, offer a reset action to restore defaults
        return [
            Actions\Action::make('resetDefaults')
                ->label('Reset to Defaults')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Reset company settings to defaults?')
                ->modalDescription('This will reset your company settings to system defaults. Custom settings will be lost.')
                ->modalSubmitActionLabel('Yes, reset settings')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->action(function () {
                    // Reset only non-critical fields to defaults
                    $this->record->update([
                        'date_format' => 'd/m/Y',
                        'time_format' => 'H:i',
                        'currency_position' => 'before',
                        'decimal_precision' => 2,
                        'thousand_separator' => ',',
                        'decimal_separator' => '.',
                        'theme_mode' => 'light',
                        'enable_notifications' => true,
                        'primary_color' => '#4f46e5',
                        'secondary_color' => '#10b981',
                    ]);
                    
                    Notification::make()
                        ->success()
                        ->title('Settings reset successfully')
                        ->send();
                        
                    $this->fillForm();
                }),
        ];
    }
    
    /**
     * Add a more descriptive success notification
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Company settings updated successfully';
    }
}
