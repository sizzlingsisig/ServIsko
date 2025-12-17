<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description'];

    /**
     * Get all listings in this category
     */
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get all category requests related to this category
     */
    public function categoryRequests()
    {
        return $this->hasMany(CategoryRequest::class);
    }


}
