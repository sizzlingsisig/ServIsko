<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Listing;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // POST /listings/{listing}/apply
    public function store(Request $request, $listingId)
    {
        $user = auth()->user();

        // Check if listing exists
        $listing = Listing::findOrFail($listingId);

        // Check if already applied
        $existing = Application::where('listing_id', $listingId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You already applied to this listing.'], 400);
        }

        // Create application
        $application = Application::create([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
            'status' => 'pending',
            'message' => $request->input('message'),
        ]);

        return response()->json([
            'message' => 'Application submitted successfully!',
            'application' => $application
        ], 201);
    }

    // (Optional) Get applications for a listing
    public function index($listingId)
    {
        $applications = Application::where('listing_id', $listingId)->get();
        return response()->json($applications);
    }

    // (Optional) Get user's own applications
    public function myApplications()
    {
        $user = auth()->user();
        return response()->json($user->applications);
    }
}

