<x-filament::section>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="text-lg font-medium mb-4">Pending Sales Orders</h3>
            
            @if($this->getPendingSalesOrders()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left rtl:text-right divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th class="px-4 py-2">SO Number</th>
                                <th class="px-4 py-2">Customer</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getPendingSalesOrders() as $salesOrder)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.admin.resources.sales-orders.edit', $salesOrder) }}" class="text-primary-600 hover:underline">
                                            {{ $salesOrder->so_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">{{ $salesOrder->customer->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $salesOrder->order_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($salesOrder->grand_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-right">
                    <a href="{{ route('filament.admin.resources.sales-orders.index', ['tableFilters[status][value]' => 'pending']) }}" class="text-primary-600 hover:underline text-sm">
                        View All Pending Sales Orders
                    </a>
                </div>
            @else
                <div class="text-gray-500 dark:text-gray-400 text-center py-4">
                    No pending sales orders found.
                </div>
            @endif
        </div>
        
        <div>
            <h3 class="text-lg font-medium mb-4">Pending Purchase Orders</h3>
            
            @if($this->getPendingPurchaseOrders()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left rtl:text-right divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th class="px-4 py-2">PO Number</th>
                                <th class="px-4 py-2">Supplier</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getPendingPurchaseOrders() as $purchaseOrder)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.admin.resources.purchase-orders.edit', $purchaseOrder) }}" class="text-primary-600 hover:underline">
                                            {{ $purchaseOrder->po_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ $purchaseOrder->order_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-2">{{ config('app.currency', '$') . number_format($purchaseOrder->grand_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-right">
                    <a href="{{ route('filament.admin.resources.purchase-orders.index', ['tableFilters[status][value]' => 'pending']) }}" class="text-primary-600 hover:underline text-sm">
                        View All Pending Purchase Orders
                    </a>
                </div>
            @else
                <div class="text-gray-500 dark:text-gray-400 text-center py-4">
                    No pending purchase orders found.
                </div>
            @endif
        </div>
    </div>
</x-filament::section>
