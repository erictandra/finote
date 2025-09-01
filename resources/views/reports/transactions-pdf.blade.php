<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report - PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .filters {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .filter-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .filter-label {
            font-weight: bold;
            width: 120px;
            display: inline-block;
        }
        
        .summary {
            margin-bottom: 25px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 11px;
            color: #666;
        }
        
        .income { color: #16a34a; }
        .expense { color: #dc2626; }
        .net-positive { color: #16a34a; }
        .net-negative { color: #dc2626; }
        
        .status-summary {
            margin-bottom: 25px;
        }
        
        .status-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .status-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .pending { background-color: #fef3c7; }
        .approved { background-color: #dcfce7; }
        .rejected { background-color: #fee2e2; }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .transactions-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }
        
        .transactions-table td {
            font-size: 10px;
        }
        
        .amount-in { color: #16a34a; font-weight: bold; }
        .amount-out { color: #dc2626; font-weight: bold; }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .status-pending { background-color: #fbbf24; color: #92400e; }
        .status-approved { background-color: #34d399; color: #065f46; }
        .status-rejected { background-color: #f87171; color: #991b1b; }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">Financial Management System</div>
        <div class="report-title">Transaction Report</div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <h3>Report Filters</h3>
        <div class="filter-row">
            <span class="filter-label">Period:</span>
            <span>{{ $filters['start_date'] }} - {{ $filters['end_date'] }}</span>
        </div>
        <div class="filter-row">
            <span class="filter-label">Wallet:</span>
            <span>{{ $filters['wallet'] }}</span>
        </div>
        <div class="filter-row">
            <span class="filter-label">Type:</span>
            <span>{{ $filters['type'] }}</span>
        </div>
        <div class="filter-row">
            <span class="filter-label">Category:</span>
            <span>{{ $filters['category'] }}</span>
        </div>
        <div class="filter-row">
            <span class="filter-label">Status:</span>
            <span>{{ $filters['status'] }}</span>
        </div>
    </div>

    <!-- Summary -->
    <div class="summary">
        <h3>Financial Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value income">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</div>
                <div class="summary-label">Total Income</div>
            </div>
            <div class="summary-item">
                <div class="summary-value expense">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
                <div class="summary-label">Total Expense</div>
            </div>
            <div class="summary-item">
                <div class="summary-value {{ $summary['net_amount'] >= 0 ? 'net-positive' : 'net-negative' }}">
                    Rp {{ number_format($summary['net_amount'], 0, ',', '.') }}
                </div>
                <div class="summary-label">Net Amount</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $summary['total_transactions'] }}</div>
                <div class="summary-label">Total Transactions</div>
            </div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="status-summary">
        <h3>Status Summary</h3>
        <div class="status-grid">
            <div class="status-item pending">
                <div class="summary-value">{{ $summary['pending_transactions'] }}</div>
                <div class="summary-label">Pending</div>
            </div>
            <div class="status-item approved">
                <div class="summary-value">{{ $summary['approved_transactions'] }}</div>
                <div class="summary-label">Approved</div>
            </div>
            <div class="status-item rejected">
                <div class="summary-value">{{ $summary['rejected_transactions'] }}</div>
                <div class="summary-label">Rejected</div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div>
        <h3>Transaction Details</h3>
        @if($transactions->count() > 0)
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Code</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 12%;">Wallet</th>
                        <th style="width: 12%;">Category</th>
                        <th style="width: 8%;">Type</th>
                        <th style="width: 12%;">Amount</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 24%;">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->code }}</td>
                            <td>{{ $transaction->date->format('d/m/Y') }}</td>
                            <td>{{ $transaction->wallet->name ?? '-' }}</td>
                            <td>{{ $transaction->category->name ?? '-' }}</td>
                            <td>
                                <span class="{{ $transaction->type === 'in' ? 'income' : 'expense' }}">
                                    {{ $transaction->type === 'in' ? 'Income' : 'Expense' }}
                                </span>
                            </td>
                            <td class="{{ $transaction->type === 'in' ? 'amount-in' : 'amount-out' }}">
                                {{ $transaction->type === 'in' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="status-badge status-{{ $transaction->status }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($transaction->remark ?? '-', 50) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 40px;">
                <p>No transactions found matching the selected criteria.</p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        Generated on {{ now()->format('d M Y H:i:s') }}
    </div>
</body>
</html>