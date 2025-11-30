<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    // Columns that can be mass assigned
    protected $fillable = [
        'seeker_user_id',
        'category_id',
        'hired_user_id',
        'title',
        'description',
        'budget',
        'status',
        'expires_at',
    ];

    // Cast attributes to native types
    protected $casts = [
        'expires_at' => 'datetime',
        'budget' => 'decimal:2',
    ];

    // The user who created the listing
    public function seeker()
    {
        return $this->belongsTo(User::class, 'seeker_user_id');
    }

    // The user assigned to the listing
    public function hiredUser()
    {
        return $this->belongsTo(User::class, 'hired_user_id');
    }

    // Listing belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Many-to-many with tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'listing_tags')
            ->withTimestamps();
    }

    // One listing can have many applications
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // One listing can have many reviews
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Check if listing has expired
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Scope to get only active (non-expired) listings
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Scope to get only expired listings
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
