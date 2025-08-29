<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionProof extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'description',
        'file',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    // Relationships
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }

    public function getFileExtensionAttribute()
    {
        return $this->file ? pathinfo($this->file, PATHINFO_EXTENSION) : null;
    }

    public function getIsImageAttribute()
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        return in_array(strtolower($this->file_extension), $imageExtensions);
    }
}
