<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SharedFile;

class Party extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'is_online',
        'join_link',
        'address',
        'movies',
        'author_id',
    ];

    protected $hidden = [
        'creation_date',
    ];

    protected $casts = [
        'movies' => 'array',
        'is_online' => 'boolean',
        'start_date' => 'datetime',
    ];

    public $timestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Participants
    public function participants()
    {
        return $this->belongsToMany(User::class, 'party_user', 'party_id', 'user_id');
    }

    // Author
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Shared files
    public function sharedFiles()
    {
        return $this->hasMany(SharedFile::class);
    }
}
