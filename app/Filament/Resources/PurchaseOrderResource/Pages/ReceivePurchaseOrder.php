<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Warehouse;
use App\Services\PurchaseOrderService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;

class ReceivePurchaseOrder extends Page
{
    use InteractsWithForms;
    
    protected static string $resource = PurchaseOrderResource::class;

    protected static string $view = 'filament.resources.purchase-order-resource.pages.receive-purchase-order';
    
    public ?array $data = [];
    
    public PurchaseOrder $record;
    
    public function mount(PurchaseOrder $record): void
    {
        $this->record = $record;
        
        $this->form->fill([
            'warehouse_id' => $record->warehouse_id,
            'receive_date' => now()->format('Y-m-d'),
            'notes' => '',
            'items' => $this->getOrderItems(),
        ]);
    }
    
    protected function getOrderItems(): array
    {
        return $this->record->items()
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('status', 'partial');
            })
            ->get()
            ->map(function (PurchaseOrderItem $item) {
                $remainingQuantity = $item->quantity - $item->received_quantity;
                
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'ordered_quantity' => $item->quantity,
                    'received_quantity' => $item->received_quantity,
                    'remaining_quantity' => $remainingQuantity,
                    'receive_quantity' => $remainingQuantity,
                    'batch_number' => '',
                    'expiry_date' => null,
                ];
            })
            ->toArray();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Receive Information')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('warehouse_id')
                                    ->label('Warehouse')
                                    ->options(Warehouse::pluck('name', 'id'))
                                    ->required(),
                                
                                DatePicker::make('receive_date')
                                    ->label('Receive Date')
                                    ->required()
                                    ->default(now()),
                            ])
                            ->columns(2),
                        
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3),
                    ]),
                
                Section::make('Items to Receive')
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                TextInput::make('product_name')
                                    ->label('Product')
                                    ->disabled(),
                                
                                Grid::make()
                                    ->schema([
                                        TextInput::make('ordered_quantity')
                                            ->label('Ordered')
                                            ->disabled(),
                                        
                                        TextInput::make('received_quantity')
                                            ->label('Already Received')
                                            ->disabled(),
                                        
                                        TextInput::make('remaining_quantity')
                                            ->label('Remaining')
                                            ->disabled(),
                                        
                                        TextInput::make('receive_quantity')
                                            ->label('Receive Now')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->maxValue(function (callable $get) {
                                                return $get('remaining_quantity');
                                            }),
                                    ])
                                    ->columns(4),
                                
                                Grid::make()
                                    ->schema([
                                        TextInput::make('batch_number')
                                            ->label('Batch Number'),
                                        
                                        DatePicker::make('expiry_date')
                                            ->label('Expiry Date'),
                                    ])
                                    ->columns(2),
                                
                                TextInput::make('id')
                                    ->hidden(),
                                
                                TextInput::make('product_id')
                                    ->hidden(),
                            ])
                            ->disabled(function () {
                                return $this->record->status === 'received' || 
                                       $this->record->status === 'cancelled';
                            })
                            ->columns(1)
                            ->dehydrated()
                    ]),
            ]);
    }
    
    public function submit()
    {
        $data = $this->form->getState();
        
        $receivedItems = [];
        $batchNumbers = [];
        $expiryDates = [];
        
        foreach ($data['items'] as $item) {
            if ($item['receive_quantity'] > 0) {
                $receivedItems[$item['id']] = $item['receive_quantity'];
                $batchNumbers[$item['id']] = $item['batch_number'] ?? null;
                $expiryDates[$item['id']] = $item['expiry_date'] ?? null;
            }
        }
        
        if (empty($receivedItems)) {
            Notification::make()
                ->title('No items to receive')
                ->warning()
                ->send();
                
            return;
        }
        
        $purchaseOrderService = app(PurchaseOrderService::class);
        $result = $purchaseOrderService->processPurchaseOrderReceipt(
            $this->record,
            $receivedItems,
            $data['warehouse_id'],
            [
                'batch_numbers' => $batchNumbers,
                'expiry_dates' => $expiryDates,
                'notes' => $data['notes'],
            ]
        );
        
        if ($result) {
            Notification::make()
                ->title('Items received successfully')
                ->success()
                ->send();
                
            $this->redirect(PurchaseOrderResource::getUrl('view', ['record' => $this->record]));
        } else {
            Notification::make()
                ->title('Failed to receive items')
                ->danger()
                ->send();
        }
    }
}
