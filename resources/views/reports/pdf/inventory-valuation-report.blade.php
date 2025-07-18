<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Valuation Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 12px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #1f2937;
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #e5e7eb;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-box {
            background-color: #f9fafb;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        .summary-box h3 {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }
        .summary-box p {
            margin: 5px 0 0;
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Valuation Report</h1>
        <p>
            Warehouse: {{ $data['warehouse_name'] }} <br>
            Generated: {{ $generatedAt }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Summary</div>
        <table>
            <tr>
                <td><strong>Total Products:</strong></td>
                <td>{{ number_format($data['summary']['total_products']) }}</td>
            </tr>
            <tr>
                <td><strong>Total Quantity:</strong></td>
                <td>{{ number_format($data['summary']['total_quantity']) }}</td>
            </tr>
            <tr>
                <td><strong>Total Value:</strong></td>
                <td>{{ $currency }}{{ number_format($data['summary']['total_value'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Inventory Items</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    @if($data['warehouse_id'] === null)
                    <th>Warehouse</th>
                    @endif
                    <th>Quantity</th>
                    <th>Unit Cost</th>
                    <th>Total Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['inventory_items'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['sku'] }}</td>
                    @if($data['warehouse_id'] === null)
                    <td>{{ $item['warehouse'] }}</td>
                    @endif
                    <td>{{ number_format($item['quantity']) }}</td>
                    <td>{{ $currency }}{{ number_format($item['unit_cost'], 2) }}</td>
                    <td>{{ $currency }}{{ number_format($item['total_value'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $data['warehouse_id'] === null ? 6 : 5 }}" style="text-align: center;">No inventory items available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} eSyncore ERP System</p>
    </div>
</body>
</html>
