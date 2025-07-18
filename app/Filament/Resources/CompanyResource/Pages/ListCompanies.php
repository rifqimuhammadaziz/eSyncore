<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Redirect;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;
    
    /**
     * Custom page title to reflect settings rather than multiple companies
     */
    public function getTitle(): string|Htmlable
    {
        return __('Company Settings');
    }
    
    /**
     * Implement singleton pattern - redirect to the edit page if a company exists
     * or to the create page if not
     */
    public function mount(): void
    {
        // Check if we already have a company record
        $company = Company::first();
        
        if ($company) {
            // Redirect to the edit page of the existing company
            $this->redirect(CompanyResource::getUrl('edit', ['record' => $company]));
        } else {
            // No company yet, redirect to create page
            $this->redirect(CompanyResource::getUrl('create'));
        }
    }
    
    /**
     * Modify header actions - we only want to create a company if none exists
     */
    protected function getHeaderActions(): array
    {
        // Only allow creating a company if none exists
        if (Company::count() === 0) {
            return [
                Actions\CreateAction::make()
                    ->label('Setup Company')
                    ->icon('heroicon-o-building-office')
                    ->color('primary'),
            ];
        }
        
        return [];
    }
}
