<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesOverviewChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Overview';
    
    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        $data = $this->getSalesData();
        
        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data['totals'],
                    'backgroundColor' => '#36a2eb',
                    'borderColor' => '#36a2eb',
                    'fill' => false,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getSalesData(): array
    {
        $periods = $this->getPeriods();
        $format = $this->getDateFormat();
        
        $salesData = SalesOrder::query()
            ->select(DB::raw("TO_CHAR(order_date, '{$format}') as period"), DB::raw('SUM(grand_total) as total'))
            ->where('order_date', '>=', $periods['start'])
            ->where('order_date', '<=', $periods['end'])
            ->where('status', 'approved')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period')
            ->toArray();
            
        $labels = [];
        $totals = [];
        
        $currentDate = clone $periods['start'];
        while ($currentDate <= $periods['end']) {
            $formattedDate = $currentDate->format($this->getDateFormatForCarbon());
            $labels[] = $formattedDate;
            $totals[] = $salesData[$formattedDate] ?? 0;
            
            if ($this->getFilter() === 'year') {
                $currentDate->addMonth();
            } elseif ($this->getFilter() === 'month') {
                $currentDate->addDay();
            } elseif ($this->getFilter() === 'week') {
                $currentDate->addDay();
            } else {
                $currentDate->addDay();
            }
        }
        
        return [
            'labels' => $labels,
            'totals' => $totals,
        ];
    }
    
    protected function getPeriods(): array
    {
        $end = Carbon::now()->endOfDay();
        
        if ($this->getFilter() === 'year') {
            $start = Carbon::now()->startOfYear();
        } elseif ($this->getFilter() === 'month') {
            $start = Carbon::now()->startOfMonth();
        } elseif ($this->getFilter() === 'week') {
            $start = Carbon::now()->startOfWeek();
        } else {
            $start = Carbon::now()->subDays(30)->startOfDay();
            $end = Carbon::now()->endOfDay();
        }
        
        return [
            'start' => $start,
            'end' => $end,
        ];
    }
    
    protected function getDateFormat(): string
    {
        if ($this->getFilter() === 'year') {
            return 'YYYY-MM';
        } elseif ($this->getFilter() === 'month') {
            return 'YYYY-MM-DD';
        } elseif ($this->getFilter() === 'week') {
            return 'YYYY-MM-DD';
        } else {
            return 'YYYY-MM-DD';
        }
    }
    
    protected function getDateFormatForCarbon(): string
    {
        if ($this->getFilter() === 'year') {
            return 'Y-m';
        } elseif ($this->getFilter() === 'month') {
            return 'Y-m-d';
        } elseif ($this->getFilter() === 'week') {
            return 'Y-m-d';
        } else {
            return 'Y-m-d';
        }
    }
    
    protected function getFilters(): ?array
    {
        return [
            'month' => 'This Month',
            'week' => 'This Week',
            'year' => 'This Year',
            'custom' => 'Last 30 Days',
        ];
    }
    
    protected function getFilter(): ?string
    {
        return $this->filter;
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": " + new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" }).format(context.raw); }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { if (Number.isInteger(value)) return new Intl.NumberFormat("en-US", { style: "currency", currency: "USD", maximumSignificantDigits: 3 }).format(value); }',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
