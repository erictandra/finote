<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Kategori Pemasukan (15 jenis)
        $incomeCategories = [
            'Saldo Awal',
            'Gaji Pokok',
            'Bonus Kinerja',
            'Tunjangan Hari Raya',
            'Freelance/Proyek',
            'Investasi Saham',
            'Dividen',
            'Bunga Bank',
            'Sewa Properti',
            'Penjualan Barang',
            'Hadiah/Gift',
            'Komisi Penjualan',
            'Royalti',
            'Bisnis Sampingan',
            'Refund/Pengembalian'
        ];

        // Kategori Pengeluaran (25 jenis)
        $expenseCategories = [
            'Makanan & Minuman',
            'Transportasi',
            'Bensin/BBM',
            'Listrik',
            'Air',
            'Gas',
            'Internet & Telepon',
            'Belanja Bulanan',
            'Pakaian',
            'Kesehatan & Obat',
            'Pendidikan',
            'Hiburan',
            'Olahraga',
            'Kecantikan/Perawatan',
            'Cicilan Rumah',
            'Cicilan Mobil',
            'Asuransi',
            'Pajak',
            'Donasi/Sedekah',
            'Perbaikan Rumah',
            'Peralatan Rumah',
            'Liburan/Wisata',
            'Hadiah/Kado',
            'Administrasi Bank',
            'Lain-lain Pengeluaran'
        ];

        // Insert Income Categories
        foreach ($incomeCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'in'
            ]);
        }

        // Insert Expense Categories
        foreach ($expenseCategories as $category) {
            Category::create([
                'name' => $category,
                'type' => 'out'
            ]);
        }
    }
}
