<?php

namespace App\Http\Controllers\Listing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\FilterListingRequest;
use App\Models\Listing;
use App\Services\Listing\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService
    ) {}

    /**
     * Public listing browse
     */
    public function index(FilterListingRequest $request)
    {
        try {
            $listings = $this->listingService->getAllListings($request->validated());
            return response()->json(['success' => true, 'data' => $listings]);
        } catch (Exception $e) {
            Log::error('Failed to fetch all listings', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch listings.'], 500);
        }
    }

    /**
     * Show listing details (public)
     */
    public function show($id): JsonResponse
    {
        try {
            $listing = Listing::with(['seeker', 'category', 'tags', 'hiredUser', 'applications'])->findOrFail($id);

            // Add expiry status to response
            $data = $listing->toArray();
            $data['is_expired'] = $listing->isExpired();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch listing', ['error' => $e->getMessage(), 'listing_id' => $id]);
            return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
        }
    }
}
