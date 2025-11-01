<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class UserProfile extends Model
{

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'profile_picture',
        'bio',
        'location',
    ];

    protected $hidden = ['user_id', 'created_at', 'updated_at'];


    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }


}
