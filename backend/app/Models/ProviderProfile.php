<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProviderProfile extends Model
{
    protected $table = 'provider_profiles';

    protected $fillable = [
        'user_id',
        'title',
        'links',
    ];

    protected $casts = [
        'links' => 'array',
    ];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(
            Skill::class,
            'provider_profile_skills',
            'provider_profile_id',
            'skill_id'
        )->withTimestamps();
    }
}
