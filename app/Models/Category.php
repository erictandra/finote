<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
    ];

    protected $casts = [
        'type' => 'string', // 'in' or 'out'
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
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
}
