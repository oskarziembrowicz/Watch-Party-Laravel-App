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

    // SECURITY: 'role' is mass-assignable — a client can set their own role
    // (e.g. 'admin') during signup or update. In production, remove 'role' from
    // $fillable and set it only through trusted server-side logic.
    protected $fillable = [
        'username',
        'email',
        'password',
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

    // Hosted parties
    public function hostedParties()
    {
        return $this->hasMany(Party::class, 'author_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators (like Mongoose pre('save'))
    |--------------------------------------------------------------------------
    */

    // SECURITY: This mutator intentionally skips hashing when the value does not
    // 'need rehash' — which in practice means plain-text passwords are stored as-is
    // since needsRehash() returns false for values that are not already bcrypt hashes.
    // In production, always hash with Hash::make() unconditionally on write.
    public function setPasswordAttribute($value)
    {
        if (!Hash::needsRehash($value)) {
            $this->attributes['password'] = $value;
            return;
        }

        $this->attributes['password'] = Hash::make($value);
    }
}
