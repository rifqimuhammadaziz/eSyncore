<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsChart extends ChartWidget
{
    protected static ?string $heading = 'Inventory Analytics';
    
    protected static ?int $sort = 3;
    
    protected function getData(): array
    {
        if ($this->getFilter() === 'top_products') {
            return $this->getTopProductsData();
        } elseif ($this->getFilter() === 'value_by_warehouse') {
            return $this->getValueByWarehouseData();
        } else {
            return $this->getStockLevelsData();
        }
    }
    
    protected function getType(): string
    {
        if ($this->getFilter() === 'top_products' || $this->getFilter() === 'value_by_warehouse') {
            return 'bar';
        }
        
        return 'pie';
    }
    
    protected function getStockLevelsData(): array
    {
        $stockStatusCounts = [
            'In Stock' => 0,
            'Low Stock' => 0,
            'Out of Stock' => 0,
        ];
        
        // Group inventory items by their stock status
        $inventoryData = Inventory::select('product_id', 'quantity_available', 'reorder_point')
            ->with('product')
            ->get()
            ->each(function ($item) use (&$stockStatusCounts) {
                if ($item->quantity_available <= 0) {
                    $stockStatusCounts['Out of Stock']++;
                } elseif ($item->reorder_point && $item->quantity_available <= $item->reorder_point) {
                    $stockStatusCounts['Low Stock']++;
                } else {
                    $stockStatusCounts['In Stock']++;
                }
            });
            
        $backgroundColor = [
            'In Stock' => '#10b981', // Green
            'Low Stock' => '#f59e0b', // Amber
            'Out of Stock' => '#ef4444', // Red
        ];
        
        return [
            'datasets' => [
                [
                    'label' => 'Stock Status',
                    'data' => array_values($stockStatusCounts),
                    'backgroundColor' => array_values($backgroundColor),
                ],
            ],
            'labels' => array_keys($stockStatusCounts),
        ];
    }
    
    protected function getTopProductsData(): array
    {
        // Get top 10 products by quantity
        $products = Inventory::select('product_id', DB::raw('SUM(quantity_available) as total_quantity'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();
            
        $labels = [];
        $quantities = [];
        
        foreach ($products as $product) {
            $labels[] = $product->product->name ?? "Product #{$product->product_id}";
            $quantities[] = $product->total_quantity;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Quantity in Stock',
                    'data' => $quantities,
                    'backgroundColor' => '#60a5fa',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getValueByWarehouseData(): array
    {
        $warehouses = Warehouse::all();
        $labels = [];
        $values = [];
        
        foreach ($warehouses as $warehouse) {
            $labels[] = $warehouse->name;
            
            // Calculate total inventory value for this warehouse
            $totalValue = Inventory::where('warehouse_id', $warehouse->id)
                ->join('products', 'products.id', '=', 'inventories.product_id')
                ->select(DB::raw('SUM(inventories.quantity_available * products.cost_price) as total_value'))
                ->first()
                ->total_value ?? 0;
                
            $values[] = $totalValue;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Inventory Value ($)',
                    'data' => $values,
                    'backgroundColor' => '#8b5cf6',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getFilters(): ?array
    {
        return [
            'stock_levels' => 'Stock Levels',
            'top_products' => 'Top Products',
            'value_by_warehouse' => 'Value by Warehouse',
        ];
    }
    
    protected function getFilter(): ?string
    {
        return $this->filter;
    }
    
    protected function getOptions(): array
    {
        if ($this->getFilter() === 'top_products' || $this->getFilter() === 'value_by_warehouse') {
            return [
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'precision' => 0,
                        ],
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'display' => true,
                    ],
                    'tooltip' => [
                        'enabled' => true,
                    ],
                ],
                'responsive' => true,
                'maintainAspectRatio' => false,
            ];
        }
        
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
