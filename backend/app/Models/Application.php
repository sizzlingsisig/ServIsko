<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listing_id',
        'status',
        'message', // optional: e.g. user's cover message
    ];

    // Each application belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Each application belongs to a listing
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}