<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderProfile extends Model
{
    protected $table = 'provider_profiles';

    protected $fillable = [
        'user_id',
        'profile_picture',
        'bio',
        'location',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}
