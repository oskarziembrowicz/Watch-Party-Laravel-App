<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'saved_movies',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'saved_movies' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function friends()
    {
        return $this->belongsToMany(
            User::class,
            'friends',
            'user_id',
            'friend_id'
        );
    }

    public function parties()
    {
        return $this->belongsToMany(Party::class);
    }

    public function archivedParties()
    {
        return $this->belongsToMany(
            Party::class,
            'archived_parties'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators (like Mongoose pre('save'))
    |--------------------------------------------------------------------------
    */

    public function setPasswordAttribute($value)
    {
        if (!Hash::needsRehash($value)) {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }
}
