<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    // GET /api/listings
    public function index()
    {
        // Eager-load related tables for better data visibility
        $listings = Listing::with(['creator', 'category', 'tags', 'assignedUser'])->get();

        return response()->json($listings);
    }
}
