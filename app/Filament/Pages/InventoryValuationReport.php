<?php

namespace App\Filament\Pages;

use App\Models\Warehouse;
use App\Services\ReportManager;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class InventoryValuationReport extends Page
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Reports';
    
    protected static ?string $navigationLabel = 'Inventory Valuation';
    
    protected static string $view = 'filament.pages.inventory-valuation-report';
    
    public ?array $reportData = null;
    
    // Form data
    public $warehouseId = null;
    
    public function mount(): void
    {
        $this->form->fill([
            'warehouseId' => $this->warehouseId,
        ]);
        
        // Generate the report by default on page load
        $this->generateReport();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Parameters')
                    ->schema([
                        Select::make('warehouseId')
                            ->label('Warehouse')
                            ->options(function () {
                                return Warehouse::where('is_active', true)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->placeholder('All Warehouses')
                            ->searchable(),
                    ])
            ]);
    }
    
    public function generateReport(): void
    {
        try {
            $data = $this->form->getState();
            $this->warehouseId = $data['warehouseId'];
            
            $this->reportData = ReportManager::generateInventoryValuationReport($this->warehouseId);
            
            Notification::make()
                ->title('Inventory valuation report generated successfully')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error generating report: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function downloadPdf()
    {
        // This would be where you implement PDF generation and download
        // For now, just show a notification that it's not implemented
        Notification::make()
            ->title('PDF Download is not implemented yet')
            ->warning()
            ->send();
    }
    
    public function downloadCsv()
    {
        // This would be where you implement CSV generation and download
        // For now, just show a notification that it's not implemented
        Notification::make()
            ->title('CSV Download is not implemented yet')
            ->warning()
            ->send();
    }
}
