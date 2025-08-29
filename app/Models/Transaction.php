<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    //
    use HasFactory, SoftDeletes;

    //
    protected $fillable = [
        'code',
        'date',
        'wallet_id',
        'category_id',
        'type',
        'status',
        'amount',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'type' => 'string', // 'in' or 'out'
        'status' => 'string', // 'pending', 'approved', 'rejected'
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Static Methods
    public static function generateCode()
    {
        $yearMonth = now()->format('ym'); // Format: yymm (contoh: 2508 untuk Agustus 2025)

        // Ambil transaksi terakhir bulan ini
        $lastTransaction = static::where('code', 'like', $yearMonth . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastTransaction) {
            // Ambil 4 digit terakhir dari code dan increment
            $lastSequence = (int) substr($lastTransaction->code, -4);
            $newSequence = $lastSequence + 1;
        } else {
            // Jika belum ada transaksi bulan ini, mulai dari 1
            $newSequence = 1;
        }

        // Format dengan 4 digit (pad dengan 0 di depan)
        return $yearMonth . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    // Boot method untuk auto-generate code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->code)) {
                $transaction->code = static::generateCode();
            }
        });
    }

    // Relationships
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionProofs()
    {
        return $this->hasMany(TransactionProof::class);
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'out');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Accessors & Mutators
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getTypeColorAttribute()
    {
        return $this->type === 'in' ? 'green' : 'red';
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }
}
