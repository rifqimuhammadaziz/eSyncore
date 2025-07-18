<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
    
    /**
     * Custom page title to reflect initial company setup
     */
    public function getTitle(): string|Htmlable
    {
        return __('Setup Company Profile');
    }
    
    /**
     * Enforce singleton pattern - redirect to edit page if a company already exists
     */
    public function mount(): void
    {
        // Check if we already have a company record
        if (Company::count() > 0) {
            // Redirect to the edit page of the existing company
            $company = Company::first();
            
            Notification::make()
                ->warning()
                ->title('Company already exists')
                ->body('You can only have one company profile. Redirecting to edit page.')
                ->send();
                
            $this->redirect(CompanyResource::getUrl('edit', ['record' => $company]));
            return;
        }
        
        // Continue with standard mount process for CreateRecord
        parent::mount();
    }
    
    /**
     * Add a more descriptive success notification
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Company profile created successfully';
    }
    
    /**
     * After creating the company, we want to redirect to the edit page
     */
    protected function getRedirectUrl(): string
    {
        return CompanyResource::getUrl('edit', ['record' => $this->record]);
    }
}
