<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    // Columns that can be mass assigned
    protected $fillable = [
        'creator_user_id',
        'category_id',
        'assigned_user_id',
        'title',
        'description',
        'budget',
        'status',
        'is_active',
    ];

    // Relationships

    // The user who created the listing
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    // The user assigned to the listing (e.g. freelancer or provider)
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // Listing belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Many-to-many with tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'listing_tags');
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
}
