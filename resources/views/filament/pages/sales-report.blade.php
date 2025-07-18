<x-filament-panels::page>
    <form wire:submit="generateReport">
        {{ $this->form }}
        
        <div class="flex justify-start mt-4">
            <x-filament::button type="submit" class="mr-2">
                Generate Report
            </x-filament::button>
        </div>
    </form>
    
    @if($reportData)
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow p-4 mb-6 dark:bg-gray-800">
                <h2 class="text-lg font-semibold mb-4">Sales Report Summary</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Period: {{ \Carbon\Carbon::parse($reportData['startDate'])->format('M d, Y') }} - 
                    {{ \Carbon\Carbon::parse($reportData['endDate'])->format('M d, Y') }}
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sales</h3>
                        <p class="text-xl font-bold mt-1">{{ config('app.currency', '$') . number_format($reportData['totalSales'], 2) }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</h3>
                        <p class="text-xl font-bold mt-1">{{ number_format($reportData['totalOrders']) }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Order Value</h3>
                        <p class="text-xl font-bold mt-1">{{ config('app.currency', '$') . number_format($reportData['averageOrderValue'], 2) }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end mt-4">
                    <x-filament::button wire:click="downloadPdf" color="secondary" class="mr-2">
                        <span class="mr-1">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        PDF Report
                    </x-filament::button>
                    <x-filament::button wire:click="downloadCsv" color="secondary">
                        <span class="mr-1">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        CSV Export
                    </x-filament::button>
                </div>
            </div>
            
            @if(count($reportData['salesData']) > 0)
                <div class="bg-white rounded-lg shadow p-4 mb-6 dark:bg-gray-800">
                    <h2 class="text-lg font-semibold mb-4">
                        Sales Breakdown by {{ ucfirst($groupBy) }}
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left rtl:text-right divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <th class="px-4 py-2">{{ ucfirst($groupBy) }}</th>
                                    <th class="px-4 py-2">Sales Amount</th>
                                    <th class="px-4 py-2">Orders</th>
                                    @if($groupBy === 'product')
                                        <th class="px-4 py-2">Quantity</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($reportData['salesData'] as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-2">{{ $item['label'] }}</td>
                                        <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($item['total_sales'], 2) }}</td>
                                        @if($groupBy !== 'product')
                                            <td class="px-4 py-2">{{ number_format($item['order_count']) }}</td>
                                        @else
                                            <td class="px-4 py-2">-</td>
                                            <td class="px-4 py-2">{{ number_format($item['total_quantity']) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 dark:bg-gray-800">
                    <h2 class="text-lg font-semibold mb-4">Top Products</h2>
                    
                    @if(count($reportData['topProducts']) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left rtl:text-right divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <th class="px-4 py-2">Product</th>
                                        <th class="px-4 py-2">Quantity</th>
                                        <th class="px-4 py-2">Sales</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($reportData['topProducts'] as $product)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-4 py-2">{{ $product->product->name ?? 'Unknown' }}</td>
                                            <td class="px-4 py-2">{{ number_format($product->total_quantity) }}</td>
                                            <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($product->total_sales, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No product data available for the selected period.</p>
                    @endif
                </div>
                
                <div class="bg-white rounded-lg shadow p-4 dark:bg-gray-800">
                    <h2 class="text-lg font-semibold mb-4">Top Customers</h2>
                    
                    @if(count($reportData['topCustomers']) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left rtl:text-right divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <th class="px-4 py-2">Customer</th>
                                        <th class="px-4 py-2">Orders</th>
                                        <th class="px-4 py-2">Sales</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($reportData['topCustomers'] as $customer)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-4 py-2">{{ $customer->customer->name ?? 'Unknown' }}</td>
                                            <td class="px-4 py-2">{{ number_format($customer->order_count) }}</td>
                                            <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($customer->total_sales, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No customer data available for the selected period.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
