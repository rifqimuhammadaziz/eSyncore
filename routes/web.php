<?php

use App\Http\Controllers\StockTransferController;
use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Print routes for ERP documents
Route::get('/stock-transfers/{stockTransfer}/print', [StockTransferController::class, 'print'])
    ->name('stock-transfers.print');

Route::get('/purchase-orders/{id}/print', function ($id) {
    $purchaseOrder = PurchaseOrder::findOrFail($id);
    return view('print.purchase-order', ['purchaseOrder' => $purchaseOrder]);
})->name('purchase-orders.print');
