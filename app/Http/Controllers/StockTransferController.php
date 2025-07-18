<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    /**
     * Display a printable version of the stock transfer
     *
     * @param StockTransfer $stockTransfer
     * @return \Illuminate\View\View
     */
    public function print(StockTransfer $stockTransfer)
    {
        return view('print.stock-transfer', [
            'stockTransfer' => $stockTransfer
        ]);
    }
}
