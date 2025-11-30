<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, HasRoles, HasOneTimePasswords;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $guard_name = 'sanctum';

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationships
     */

    // A user can have many listings
        public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'seeker_user_id');
    }


    // A user can have many applications
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    public function profile(): HasOne{
        return $this->hasOne(UserProfile::class);
    }

    public function providerProfile(): HasOne{
        return $this->hasOne(ProviderProfile::class);
    }

    public function hiredListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'hired_user_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'provider_id');
    }
}
