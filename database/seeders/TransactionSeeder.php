<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wallets = Wallet::all();
        $incomeCategories = Category::where('type', 'in')->pluck('id')->toArray();
        $expenseCategories = Category::where('type', 'out')->pluck('id')->toArray();

        // Step 1: Create initial balance transactions (January 1st)
        $this->createInitialBalanceTransactions($wallets, $incomeCategories);

        // Step 2: Get start date (January 2nd) and end date (today)
        $startDate = Carbon::now()->startOfYear()->addDay(); // Start from Jan 2nd
        $endDate = Carbon::now();

        // Step 3: Create daily transactions
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Create 5 transactions per day
            for ($i = 0; $i < 5; $i++) {
                $this->createSingleTransaction($currentDate, $wallets, $incomeCategories, $expenseCategories);
            }

            $currentDate->addDay();
        }

        echo "Seeded initial balance transactions on January 1st\n";
        echo "Seeded daily transactions from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";
        echo "Total transactions created: " . Transaction::count() . "\n";

        // Show final wallet balances
        $this->showWalletBalances();
    }

    private function createInitialBalanceTransactions($wallets, $incomeCategories): void
    {
        $initialBalances = [
            'CASH' => 5000000,
            'BCA' => 15000000,
            'BRI' => 8000000,
            'BNI' => 12000000,
            'GOPAY' => 2500000,
            'MANDIRI' => 10000000,
        ];

        $january1st = Carbon::now()->startOfYear();

        foreach ($wallets as $wallet) {
            $initialAmount = $initialBalances[$wallet->name];

            // Create initial balance transaction
            $transaction = new Transaction();
            $transaction->date = $january1st;
            $transaction->wallet_id = $wallet->id;
            $transaction->category_id = fake()->randomElement($incomeCategories);
            $transaction->type = 'in';
            $transaction->status = 'approved';
            $transaction->amount = $initialAmount;
            $transaction->remark = "Saldo Awal {$wallet->name} - " . date('Y');
            $transaction->code = $this->generateTransactionCode($january1st);

            $transaction->save();

            // Update wallet balance
            $wallet->increment('balance', $initialAmount);
        }
    }

    private function createSingleTransaction($date, $wallets, $incomeCategories, $expenseCategories): void
    {
        // 40% chance for income, 60% chance for expense
        $isIncome = fake()->boolean(40);

        $wallet = $wallets->random();
        $type = $isIncome ? 'in' : 'out';
        $categoryId = $isIncome
            ? fake()->randomElement($incomeCategories)
            : fake()->randomElement($expenseCategories);

        // Generate appropriate amount based on type
        if ($isIncome) {
            $amount = fake()->numberBetween(100000, 3000000); // 100k - 3M for income
        } else {
            // For expenses, make sure wallet won't go negative
            $maxExpense = min(1500000, $wallet->balance * 0.3); // Max 30% of balance or 1.5M
            $amount = fake()->numberBetween(25000, max(25000, $maxExpense));
        }

        // 85% approved, 10% pending, 5% rejected
        $statusWeight = fake()->randomElement([
            ...array_fill(0, 85, 'approved'),
            ...array_fill(0, 10, 'pending'),
            ...array_fill(0, 5, 'rejected')
        ]);

        // Create transaction without triggering model events (we'll handle balance manually)
        $transaction = new Transaction();
        $transaction->date = $date;
        $transaction->wallet_id = $wallet->id;
        $transaction->category_id = $categoryId;
        $transaction->type = $type;
        $transaction->status = $statusWeight;
        $transaction->amount = $amount;
        $transaction->remark = fake()->optional(0.6)->sentence(8);

        // Generate code manually
        $transaction->code = $this->generateTransactionCode($date);

        $transaction->save();

        // Update wallet balance if approved
        if ($statusWeight === 'approved') {
            $this->updateWalletBalance($wallet, $type, $amount);
        }
    }

    private function generateTransactionCode(Carbon $date): string
    {
        $yearMonth = $date->format('ym');

        // Get last transaction code for this month
        $lastTransaction = Transaction::where('code', 'like', $yearMonth . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastSequence = (int) substr($lastTransaction->code, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $yearMonth . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    private function updateWalletBalance(Wallet $wallet, string $type, float $amount): void
    {
        if ($type === 'in') {
            // Income: add to balance
            $wallet->increment('balance', $amount);
        } else {
            // Expense: subtract from balance, but ensure it doesn't go negative
            $newBalance = $wallet->balance - $amount;
            if ($newBalance >= 0) {
                $wallet->decrement('balance', $amount);
            } else {
                // If would go negative, only deduct what's available
                $wallet->update(['balance' => 0]);
            }
        }
    }

    private function showWalletBalances(): void
    {
        echo "\n=== Final Wallet Balances ===\n";
        $wallets = Wallet::all();
        foreach ($wallets as $wallet) {
            echo sprintf(
                "%-10s: Rp %s\n",
                $wallet->name,
                number_format($wallet->balance, 0, ',', '.')
            );
        }
        echo "=============================\n";
    }
}
