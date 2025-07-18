<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReportManager
{
    /**
     * Generate sales report by date range
     */
    public static function generateSalesReport(string $startDate, string $endDate, ?string $groupBy = null): array
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        
        $query = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');
            
        $totalSales = $query->sum('grand_total');
        $totalOrders = $query->count();
        
        $salesData = [];
        
        // Group data according to selected option
        switch ($groupBy) {
            case 'daily':
                $salesData = self::groupSalesDataByDaily($startDate, $endDate);
                break;
            case 'monthly':
                $salesData = self::groupSalesDataByMonthly($startDate, $endDate);
                break;
            case 'product':
                $salesData = self::groupSalesDataByProduct($startDate, $endDate);
                break;
            case 'customer':
                $salesData = self::groupSalesDataByCustomer($startDate, $endDate);
                break;
            default:
                $salesData = [];
        }
        
        // Calculate average order value
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        
        // Top 5 best-selling products
        $topProducts = self::getTopSellingProducts($startDate, $endDate, 5);
        
        // Top 5 customers by sales value
        $topCustomers = self::getTopCustomers($startDate, $endDate, 5);
        
        return [
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'salesData' => $salesData,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
    
    /**
     * Group sales data by day
     */
    private static function groupSalesDataByDaily(Carbon $startDate, Carbon $endDate): array
    {
        $salesByDay = SalesOrder::select(
                DB::raw('DATE(order_date) as date'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date')
            ->get();
        
        $result = [];
        foreach ($salesByDay as $day) {
            $result[] = [
                'label' => Carbon::parse($day->date)->format('M d, Y'),
                'total_sales' => $day->total_sales,
                'order_count' => $day->order_count,
            ];
        }
        
        return $result;
    }
    
    /**
     * Group sales data by month
     */
    private static function groupSalesDataByMonthly(Carbon $startDate, Carbon $endDate): array
    {
        $salesByMonth = SalesOrder::select(
                DB::raw('YEAR(order_date) as year'),
                DB::raw('MONTH(order_date) as month'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy(DB::raw('YEAR(order_date)'), DB::raw('MONTH(order_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        $result = [];
        foreach ($salesByMonth as $month) {
            $monthDate = Carbon::createFromDate($month->year, $month->month, 1);
            $result[] = [
                'label' => $monthDate->format('M Y'),
                'total_sales' => $month->total_sales,
                'order_count' => $month->order_count,
            ];
        }
        
        return $result;
    }
    
    /**
     * Group sales data by product
     */
    private static function groupSalesDataByProduct(Carbon $startDate, Carbon $endDate): array
    {
        $salesByProduct = SalesOrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_sales')
            )
            ->whereHas('salesOrder', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_date', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled');
            })
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();
        
        $result = [];
        foreach ($salesByProduct as $item) {
            $result[] = [
                'label' => $item->product->name ?? 'Unknown Product',
                'total_sales' => $item->total_sales,
                'total_quantity' => $item->total_quantity,
            ];
        }
        
        return $result;
    }
    
    /**
     * Group sales data by customer
     */
    private static function groupSalesDataByCustomer(Carbon $startDate, Carbon $endDate): array
    {
        $salesByCustomer = SalesOrder::select(
                'customer_id',
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('customer_id')
            ->with('customer:id,name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();
        
        $result = [];
        foreach ($salesByCustomer as $customer) {
            $result[] = [
                'label' => $customer->customer->name ?? 'Unknown Customer',
                'total_sales' => $customer->total_sales,
                'order_count' => $customer->order_count,
            ];
        }
        
        return $result;
    }
    
    /**
     * Get top selling products
     */
    public static function getTopSellingProducts(Carbon $startDate, Carbon $endDate, int $limit = 5): Collection
    {
        return SalesOrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_sales')
            )
            ->whereHas('salesOrder', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_date', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled');
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get top customers by sales value
     */
    public static function getTopCustomers(Carbon $startDate, Carbon $endDate, int $limit = 5): Collection
    {
        return SalesOrder::select(
                'customer_id',
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('customer_id')
            ->with('customer')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Generate inventory valuation report
     */
    public static function generateInventoryValuationReport(?int $warehouseId = null): array
    {
        $query = DB::table('inventories')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'warehouses.name as warehouse_name',
                'inventories.quantity_available',
                'products.purchase_price',
                DB::raw('inventories.quantity_available * products.purchase_price as total_value')
            )
            ->where('inventories.deleted_at', null)
            ->where('products.deleted_at', null)
            ->where('warehouses.deleted_at', null);
            
        if ($warehouseId) {
            $query->where('warehouses.id', $warehouseId);
        }
        
        $inventoryItems = $query->orderBy('products.name')->get();
        
        $totalValue = $inventoryItems->sum('total_value');
        
        return [
            'items' => $inventoryItems,
            'totalValue' => $totalValue,
            'reportDate' => now(),
        ];
    }
}
