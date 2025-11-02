<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderLink extends Model
{
    protected $table = 'provider_links';

    protected $fillable = [
        'provider_profile_id',
        'title',
        'url',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    protected $hidden = [
        'provider_profile_id',
    ];

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}
