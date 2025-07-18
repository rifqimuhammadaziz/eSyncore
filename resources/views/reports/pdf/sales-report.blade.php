<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
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
        <h1>Sales Report</h1>
        <p>
            Period: {{ $filters['date_from'] }} to {{ $filters['date_to'] }} <br>
            Grouped by: {{ ucfirst($filters['group_by'] ?? 'Daily') }} <br>
            Generated: {{ $generatedAt }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Summary</div>
        <table>
            <tr>
                <td><strong>Total Orders:</strong></td>
                <td>{{ number_format($data['summary']['total_orders']) }}</td>
            </tr>
            <tr>
                <td><strong>Total Sales:</strong></td>
                <td>{{ $currency }}{{ number_format($data['summary']['total_sales'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Average Order Value:</strong></td>
                <td>{{ $currency }}{{ number_format($data['summary']['average_order_value'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Sales Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Orders</th>
                    <th>Sales</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['breakdown'] as $period => $breakdown)
                <tr>
                    <td>{{ $period }}</td>
                    <td>{{ number_format($breakdown['orders']) }}</td>
                    <td>{{ $currency }}{{ number_format($breakdown['sales'], 2) }}</td>
                    <td>{{ $currency }}{{ number_format($breakdown['average'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(!empty($data['top_products']))
    <div class="section">
        <div class="section-title">Top Products</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Sales</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_products'] as $product)
                <tr>
                    <td>{{ $product['name'] }}</td>
                    <td>{{ number_format($product['quantity']) }}</td>
                    <td>{{ $currency }}{{ number_format($product['sales'], 2) }}</td>
                    <td>{{ number_format($product['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($data['top_customers']))
    <div class="section">
        <div class="section-title">Top Customers</div>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Orders</th>
                    <th>Sales</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_customers'] as $customer)
                <tr>
                    <td>{{ $customer['name'] }}</td>
                    <td>{{ number_format($customer['orders']) }}</td>
                    <td>{{ $currency }}{{ number_format($customer['sales'], 2) }}</td>
                    <td>{{ number_format($customer['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>© {{ date('Y') }} eSyncore ERP System</p>
    </div>
</body>
</html>
