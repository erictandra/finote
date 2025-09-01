<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    //

    public function transactions(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'wallet_id' => 'nullable|exists:wallets,id',
            'type' => 'nullable|in:in,out',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:all,pending,approved,rejected',
            'export' => 'nullable|in:pdf',
        ]);

        // Build query
        $query = Transaction::with(['wallet', 'category'])
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->wallet_id) {
            $query->where('wallet_id', $request->wallet_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_income' => $transactions->where('type', 'in')->sum('amount'),
            'total_expense' => $transactions->where('type', 'out')->sum('amount'),
            'net_amount' => $transactions->where('type', 'in')->sum('amount') - $transactions->where('type', 'out')->sum('amount'),
            'total_transactions' => $transactions->count(),
            'pending_transactions' => $transactions->where('status', 'pending')->count(),
            'approved_transactions' => $transactions->where('status', 'approved')->count(),
            'rejected_transactions' => $transactions->where('status', 'rejected')->count(),
        ];

        // Get filter data for display
        $filters = [
            'start_date' => Carbon::parse($request->start_date)->format('d M Y'),
            'end_date' => Carbon::parse($request->end_date)->format('d M Y'),
            'wallet' => $request->wallet_id ? Wallet::find($request->wallet_id)?->name : 'All Wallets',
            'type' => $request->type ? ($request->type === 'in' ? 'Income' : 'Expense') : 'All Types',
            'category' => $request->category_id ? Category::find($request->category_id)?->name : 'All Categories',
            'status' => ucfirst($request->status),
        ];

        // Export PDF if requested
        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.transactions-pdf', [
                'transactions' => $transactions,
                'summary' => $summary,
                'filters' => $filters,
            ]);

            $filename = 'transaction-report-' . $request->start_date . '-to-' . $request->end_date . '.pdf';
            return $pdf->download($filename);
        }

        return view('reports.transactions', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }
}
