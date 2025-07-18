<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportExportService
{
    /**
     * Export Sales Report to PDF
     *
     * @param array $filters
     * @return Response
     */
    public function exportSalesReportToPdf(array $filters): Response
    {
        $reportData = $this->generateSalesReportData($filters);
        
        $pdf = PDF::loadView('reports.pdf.sales-report', [
            'data' => $reportData,
            'filters' => $filters,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
            'currency' => config('app.currency', '$'),
        ]);
        
        return $pdf->download('sales_report_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
    
    /**
     * Export Sales Report to CSV
     *
     * @param array $filters
     * @return Response
     */
    public function exportSalesReportToCsv(array $filters): Response
    {
        $reportData = $this->generateSalesReportData($filters);
        $filename = 'sales_report_' . Carbon::now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $handle = fopen('php://temp', 'r+');
        
        // Add headers to CSV
        fputcsv($handle, ['Sales Report', '', '', '']);
        fputcsv($handle, ['Generated At', Carbon::now()->format('Y-m-d H:i:s'), '', '']);
        fputcsv($handle, ['Period', $filters['date_from'] . ' to ' . $filters['date_to'], '', '']);
        fputcsv($handle, ['Group By', $filters['group_by'] ?? 'All', '', '']);
        fputcsv($handle, ['', '', '', '']);
        
        // Add summary data
        fputcsv($handle, ['Summary', '', '', '']);
        fputcsv($handle, ['Total Orders', $reportData['summary']['total_orders'], '', '']);
        fputcsv($handle, ['Total Sales', $reportData['summary']['total_sales'], '', '']);
        fputcsv($handle, ['Average Order Value', $reportData['summary']['average_order_value'], '', '']);
        fputcsv($handle, ['', '', '', '']);
        
        // Add sales breakdown
        fputcsv($handle, ['Sales Breakdown', '', '', '']);
        fputcsv($handle, ['Period', 'Orders', 'Sales', 'Average']);
        
        foreach ($reportData['breakdown'] as $period => $data) {
            fputcsv($handle, [
                $period,
                $data['orders'],
                $data['sales'],
                $data['average'],
            ]);
        }
        
        fputcsv($handle, ['', '', '', '']);
        
        // Add top products
        if (!empty($reportData['top_products'])) {
            fputcsv($handle, ['Top Products', '', '', '']);
            fputcsv($handle, ['Product', 'Quantity', 'Sales', '% of Total']);
            
            foreach ($reportData['top_products'] as $product) {
                fputcsv($handle, [
                    $product['name'],
                    $product['quantity'],
                    $product['sales'],
                    $product['percentage'],
                ]);
            }
            
            fputcsv($handle, ['', '', '', '']);
        }
        
        // Add top customers
        if (!empty($reportData['top_customers'])) {
            fputcsv($handle, ['Top Customers', '', '', '']);
            fputcsv($handle, ['Customer', 'Orders', 'Sales', '% of Total']);
            
            foreach ($reportData['top_customers'] as $customer) {
                fputcsv($handle, [
                    $customer['name'],
                    $customer['orders'],
                    $customer['sales'],
                    $customer['percentage'],
                ]);
            }
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200, $headers);
    }
    
    /**
     * Export Inventory Valuation Report to PDF
     *
     * @param array $filters
     * @return Response
     */
    public function exportInventoryValuationReportToPdf(array $filters): Response
    {
        $reportData = $this->generateInventoryValuationReportData($filters);
        
        $pdf = PDF::loadView('reports.pdf.inventory-valuation-report', [
            'data' => $reportData,
            'filters' => $filters,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
            'currency' => config('app.currency', '$'),
        ]);
        
        return $pdf->download('inventory_valuation_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
    
    /**
     * Export Inventory Valuation Report to CSV
     *
     * @param array $filters
     * @return Response
     */
    public function exportInventoryValuationReportToCsv(array $filters): Response
    {
        $reportData = $this->generateInventoryValuationReportData($filters);
        $filename = 'inventory_valuation_' . Carbon::now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        
        $handle = fopen('php://temp', 'r+');
        
        // Add headers to CSV
        fputcsv($handle, ['Inventory Valuation Report', '', '', '', '']);
        fputcsv($handle, ['Generated At', Carbon::now()->format('Y-m-d H:i:s'), '', '', '']);
        fputcsv($handle, ['Warehouse', $reportData['warehouse_name'] ?? 'All Warehouses', '', '', '']);
        fputcsv($handle, ['', '', '', '', '']);
        
        // Add summary data
        fputcsv($handle, ['Summary', '', '', '', '']);
        fputcsv($handle, ['Total Products', $reportData['summary']['total_products'], '', '', '']);
        fputcsv($handle, ['Total Quantity', $reportData['summary']['total_quantity'], '', '', '']);
        fputcsv($handle, ['Total Value', $reportData['summary']['total_value'], '', '', '']);
        fputcsv($handle, ['', '', '', '', '']);
        
        // Add inventory items
        fputcsv($handle, ['Inventory Items', '', '', '', '']);
        fputcsv($handle, ['Product', 'SKU', 'Quantity', 'Unit Cost', 'Total Value']);
        
        foreach ($reportData['inventory_items'] as $item) {
            fputcsv($handle, [
                $item['name'],
                $item['sku'],
                $item['quantity'],
                $item['unit_cost'],
                $item['total_value'],
            ]);
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content, 200, $headers);
    }
    
    /**
     * Generate Sales Report Data
     *
     * @param array $filters
     * @return array
     */
    private function generateSalesReportData(array $filters): array
    {
        $dateFrom = Carbon::parse($filters['date_from']);
        $dateTo = Carbon::parse($filters['date_to']);
        $groupBy = $filters['group_by'] ?? 'daily';
        
        // Base query for sales orders
        $query = SalesOrder::whereBetween('order_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
                    ->where('status', 'approved');
        
        // Calculate summary data
        $summary = [
            'total_orders' => $query->count(),
            'total_sales' => $query->sum('total_amount'),
            'average_order_value' => $query->avg('total_amount') ?? 0,
        ];
        
        // Get sales breakdown by the specified group
        $breakdown = $this->getSalesBreakdown($dateFrom, $dateTo, $groupBy);
        
        // Get top products
        $topProducts = $this->getTopSellingProducts($dateFrom, $dateTo);
        
        // Get top customers
        $topCustomers = $this->getTopCustomers($dateFrom, $dateTo);
        
        return [
            'summary' => $summary,
            'breakdown' => $breakdown,
            'top_products' => $topProducts,
            'top_customers' => $topCustomers,
        ];
    }
    
    /**
     * Get sales breakdown by period
     *
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @param string $groupBy
     * @return array
     */
    private function getSalesBreakdown(Carbon $dateFrom, Carbon $dateTo, string $groupBy): array
    {
        $format = '%Y-%m-%d';
        $periodFormat = 'Y-m-d';
        
        if ($groupBy === 'monthly') {
            $format = '%Y-%m';
            $periodFormat = 'Y-m';
        } elseif ($groupBy === 'yearly') {
            $format = '%Y';
            $periodFormat = 'Y';
        }
        
        $sales = SalesOrder::select(
                DB::raw("DATE_FORMAT(order_date, '{$format}') as period"),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as sales')
            )
            ->whereBetween('order_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->where('status', 'approved')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
            
        $breakdown = [];
        
        foreach ($sales as $sale) {
            $breakdown[$sale->period] = [
                'orders' => $sale->orders,
                'sales' => $sale->sales,
                'average' => $sale->orders > 0 ? $sale->sales / $sale->orders : 0,
            ];
        }
        
        return $breakdown;
    }
    
    /**
     * Get top selling products
     *
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @param int $limit
     * @return Collection
     */
    private function getTopSellingProducts(Carbon $dateFrom, Carbon $dateTo, int $limit = 10): Collection
    {
        $totalSales = SalesOrder::whereBetween('order_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
                        ->where('status', 'approved')
                        ->sum('total_amount') ?? 0;
        
        $topProducts = SalesOrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as total_sales')
            )
            ->whereHas('salesOrder', function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('order_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
                      ->where('status', 'approved');
            })
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get()
            ->map(function ($item) use ($totalSales) {
                return [
                    'name' => $item->product->name ?? "Product #{$item->product_id}",
                    'quantity' => $item->total_quantity,
                    'sales' => $item->total_sales,
                    'percentage' => $totalSales > 0 ? round(($item->total_sales / $totalSales) * 100, 2) : 0,
                ];
            });
            
        return $topProducts;
    }
    
    /**
     * Get top customers
     *
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @param int $limit
     * @return Collection
     */
    private function getTopCustomers(Carbon $dateFrom, Carbon $dateTo, int $limit = 10): Collection
    {
        $totalSales = SalesOrder::whereBetween('order_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
                        ->where('status', 'approved')
                        ->sum('total_amount') ?? 0;
        
        $topCustomers = SalesOrder::select(
                'customer_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->whereBetween('order_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->where('status', 'approved')
            ->with('customer:id,first_name,last_name,company_name')
            ->groupBy('customer_id')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get()
            ->map(function ($order) use ($totalSales) {
                $customerName = '';
                if ($order->customer) {
                    $customerName = $order->customer->company_name ?: 
                        $order->customer->first_name . ' ' . $order->customer->last_name;
                } else {
                    $customerName = "Customer #{$order->customer_id}";
                }
                
                return [
                    'name' => $customerName,
                    'orders' => $order->total_orders,
                    'sales' => $order->total_sales,
                    'percentage' => $totalSales > 0 ? round(($order->total_sales / $totalSales) * 100, 2) : 0,
                ];
            });
            
        return $topCustomers;
    }
    
    /**
     * Generate Inventory Valuation Report Data
     *
     * @param array $filters
     * @return array
     */
    private function generateInventoryValuationReportData(array $filters): array
    {
        $warehouseId = $filters['warehouse_id'] ?? null;
        
        // Get warehouse info if specified
        $warehouseName = 'All Warehouses';
        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
            $warehouseName = $warehouse ? $warehouse->name : 'Unknown Warehouse';
        }
        
        // Base query for inventory
        $query = Inventory::query()
            ->with('product', 'warehouse');
            
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        // Get inventory items
        $inventoryItems = $query->get()
            ->map(function ($item) {
                $unitCost = $item->product->cost_price ?? 0;
                $totalValue = $item->quantity_available * $unitCost;
                
                return [
                    'name' => $item->product->name ?? "Product #{$item->product_id}",
                    'sku' => $item->product->sku ?? '',
                    'warehouse' => $item->warehouse->name ?? "Warehouse #{$item->warehouse_id}",
                    'quantity' => $item->quantity_available,
                    'unit_cost' => $unitCost,
                    'total_value' => $totalValue,
                ];
            });
            
        // Calculate summary
        $summary = [
            'total_products' => $inventoryItems->count(),
            'total_quantity' => $inventoryItems->sum('quantity'),
            'total_value' => $inventoryItems->sum('total_value'),
        ];
        
        return [
            'warehouse_id' => $warehouseId,
            'warehouse_name' => $warehouseName,
            'summary' => $summary,
            'inventory_items' => $inventoryItems,
        ];
    }
}
