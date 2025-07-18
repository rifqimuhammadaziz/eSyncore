<x-filament-panels::page>
    <x-filament::section>
        <div class="flex justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">Receive Purchase Order: {{ $record->po_number }}</h2>
                <p class="text-gray-500">Supplier: {{ $record->supplier->name }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-gray-500">Status: 
                    <span @class([
                        'px-2 py-1 rounded-full text-xs font-medium',
                        'bg-gray-100 text-gray-800' => $record->status === 'draft',
                        'bg-blue-100 text-blue-800' => $record->status === 'pending',
                        'bg-green-100 text-green-800' => $record->status === 'approved',
                        'bg-yellow-100 text-yellow-800' => $record->status === 'partial',
                        'bg-green-100 text-green-800' => $record->status === 'received',
                        'bg-red-100 text-red-800' => $record->status === 'cancelled',
                    ])>
                        {{ ucfirst($record->status) }}
                    </span>
                </p>
                <p class="text-sm text-gray-500">Order Date: {{ $record->order_date->format('M d, Y') }}</p>
                @if($record->expected_date)
                    <p class="text-sm text-gray-500">Expected Delivery: {{ $record->expected_date->format('M d, Y') }}</p>
                @endif
            </div>
        </div>
    </x-filament::section>
    
    <form wire:submit="submit">
        {{ $this->form }}
        
        <div class="mt-6 flex justify-end">
            <x-filament::button
                type="submit"
                :disabled="$record->status === 'received' || $record->status === 'cancelled'"
            >
                Receive Items
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
