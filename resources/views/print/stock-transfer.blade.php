<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer #{{ $stockTransfer->transfer_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        h1 {
            font-size: 24px;
            margin: 0;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .transfer-details {
            width: 100%;
            margin-bottom: 20px;
        }
        .transfer-details td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .transfer-details .label {
            font-weight: bold;
            width: 150px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        table.items td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .subtotal {
            text-align: right;
            padding: 5px;
        }
        .notes {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 30%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        @media print {
            body {
                padding: 0;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name', 'eSyncore ERP') }}</div>
        <div class="document-title">Stock Transfer</div>
    </div>
    
    <table class="transfer-details">
        <tr>
            <td class="label">Transfer Number:</td>
            <td>{{ $stockTransfer->transfer_number }}</td>
            <td class="label">Status:</td>
            <td>{{ ucfirst($stockTransfer->status) }}</td>
        </tr>
        <tr>
            <td class="label">Transfer Date:</td>
            <td>{{ $stockTransfer->transfer_date->format('M d, Y') }}</td>
            <td class="label">Created By:</td>
            <td>{{ $stockTransfer->creator ? $stockTransfer->creator->fullName : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Source Warehouse:</td>
            <td>{{ $stockTransfer->sourceWarehouse->name }}</td>
            <td class="label">Approved By:</td>
            <td>{{ $stockTransfer->approver ? $stockTransfer->approver->fullName : 'Not approved yet' }}</td>
        </tr>
        <tr>
            <td class="label">Destination Warehouse:</td>
            <td>{{ $stockTransfer->destinationWarehouse->name }}</td>
            <td class="label">Approved At:</td>
            <td>{{ $stockTransfer->approved_at ? $stockTransfer->approved_at->format('M d, Y H:i') : 'Not approved yet' }}</td>
        </tr>
    </table>
    
    <h3>Transfer Items</h3>
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Batch Number</th>
                <th>Expiry Date</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockTransfer->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div>{{ $item->product->name }}</div>
                        <div class="text-gray-500 text-sm">{{ $item->product->sku }}</div>
                    </td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->batch_number ?? 'N/A' }}</td>
                    <td>{{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $item->notes ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    @if($stockTransfer->notes)
        <div class="notes">
            <h3>Notes</h3>
            <p>{{ $stockTransfer->notes }}</p>
        </div>
    @endif
    
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Prepared By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Approved By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Received By</div>
        </div>
    </div>
    
    <div class="footer">
        <p>Generated on {{ now()->format('M d, Y H:i') }} | {{ config('app.name', 'eSyncore ERP') }}</p>
    </div>
</body>
</html>
