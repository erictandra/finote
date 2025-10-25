<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $wallets = [
            'CASH',
            'BCA',
            'BRI',
            'BNI',
            'GOPAY',
            'MANDIRI',
        ];

        foreach ($wallets as $walletName) {
            Wallet::create([
                'name' => $walletName,
                'balance' => 0 // Start with zero balance
            ]);
        }
    }
}
