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
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold mb-1">Inventory Valuation Report</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-2">
                            Report Date: {{ \Carbon\Carbon::parse($reportData['reportDate'])->format('M d, Y H:i') }}
                        </p>
                        <p class="text-gray-600 dark:text-gray-400">
                            Warehouse: {{ $warehouseId ? (App\Models\Warehouse::find($warehouseId)->name ?? 'Not Found') : 'All Warehouses' }}
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Inventory Value</h3>
                        <p class="text-xl font-bold mt-1">{{ config('app.currency', '$') . number_format($reportData['totalValue'], 2) }}</p>
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
            
            @if(count($reportData['items']) > 0)
                <div class="bg-white rounded-lg shadow p-4 mb-6 dark:bg-gray-800">
                    <h2 class="text-lg font-semibold mb-4">Inventory Items Valuation</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left rtl:text-right divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <th class="px-4 py-2">SKU</th>
                                    <th class="px-4 py-2">Product</th>
                                    <th class="px-4 py-2">Warehouse</th>
                                    <th class="px-4 py-2">Quantity</th>
                                    <th class="px-4 py-2">Unit Cost</th>
                                    <th class="px-4 py-2">Total Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($reportData['items'] as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-2">{{ $item->sku }}</td>
                                        <td class="px-4 py-2">{{ $item->product_name }}</td>
                                        <td class="px-4 py-2">{{ $item->warehouse_name }}</td>
                                        <td class="px-4 py-2">{{ number_format($item->quantity_available, 2) }}</td>
                                        <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($item->purchase_price, 2) }}</td>
                                        <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($item->total_value, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold bg-gray-50 dark:bg-gray-800">
                                    <td class="px-4 py-2" colspan="5">Total</td>
                                    <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($reportData['totalValue'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-4 mb-6 dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No inventory data available for the selected warehouse.</p>
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
