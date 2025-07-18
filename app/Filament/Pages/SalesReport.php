<?php

namespace App\Filament\Pages;

use App\Services\ReportManager;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;

class SalesReport extends Page
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationGroup = 'Reports';
    
    protected static ?string $navigationLabel = 'Sales Report';
    
    protected static string $view = 'filament.pages.sales-report';
    
    public ?array $reportData = null;
    
    // Form data
    public $startDate;
    public $endDate;
    public $groupBy = 'daily';
    
    public function mount(): void
    {
        $this->startDate = now()->subMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        $this->form->fill([
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'groupBy' => $this->groupBy,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Parameters')
                    ->schema([
                        DatePicker::make('startDate')
                            ->required()
                            ->label('Start Date')
                            ->default(now()->subMonth()),
                        
                        DatePicker::make('endDate')
                            ->required()
                            ->label('End Date')
                            ->default(now()),
                            
                        Select::make('groupBy')
                            ->options([
                                'daily' => 'Daily',
                                'monthly' => 'Monthly',
                                'product' => 'By Product',
                                'customer' => 'By Customer',
                            ])
                            ->default('daily')
                            ->required(),
                    ])
                    ->columns(3)
            ]);
    }
    
    public function generateReport(): void
    {
        try {
            $data = $this->form->getState();
            
            // Validate dates
            if (Carbon::parse($data['endDate'])->isBefore(Carbon::parse($data['startDate']))) {
                throw new Halt('End date cannot be before start date.');
            }
            
            $this->startDate = $data['startDate'];
            $this->endDate = $data['endDate'];
            $this->groupBy = $data['groupBy'];
            
            $this->reportData = ReportManager::generateSalesReport(
                $this->startDate,
                $this->endDate,
                $this->groupBy
            );
            
            Notification::make()
                ->title('Report generated successfully')
                ->success()
                ->send();
                
        } catch (Halt $exception) {
            Notification::make()
                ->title($exception->getMessage())
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
