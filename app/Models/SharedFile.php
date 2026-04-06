<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SharedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_id',
        'uploaded_by',
        'original_name',
        'stored_path',
        'mime_type',
        'size',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
