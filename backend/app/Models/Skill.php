<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $table = 'skills';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the provider profiles that have this skill
     */
    public function providerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            ProviderProfile::class,
            'provider_profile_skills',  // ← Changed from provider_profile_skill
            'skill_id',
            'provider_profile_id'
        )->withTimestamps();
    }
}
