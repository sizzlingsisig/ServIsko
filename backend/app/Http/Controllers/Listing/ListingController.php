<?php

namespace App\Http\Controllers\Listing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Http\Requests\Listing\FilterListingRequest;
use App\Models\Listing;
use App\Services\Listing\ListingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ListingController extends Controller
{
    protected $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * Get all listings for the authenticated user
     * GET /api/listings
     */
    public function index(FilterListingRequest $request)
    {
        try {
            $listings = $this->listingService->getUserListings(
                auth()->id(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $listings,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch listings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show a specific listing
     * GET /api/listings/{id}
     */
    public function show($id)
    {
        try {
            $listing = Listing::where('seeker_user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            // Add expiry status to response
            $listing->load(['seeker', 'category', 'tags', 'hiredUser', 'applications']);
            $listing->is_expired = $listing->isExpired();

            return response()->json([
                'success' => true,
                'data' => $listing,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch listing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new listing
     * POST /api/listings
     */
    public function store(StoreListingRequest $request)
    {
        try {
            $listing = DB::transaction(function () use ($request) {
                return $this->listingService->createListing(
                    $request->validated(),
                    auth()->id()
                );
            });

            Log::info('Listing created', ['listing_id' => $listing->id, 'seeker_user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Listing created successfully.',
                'data' => $listing->load(['tags', 'category']),
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create listing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update listing details
     * PUT/PATCH /api/listings/{id}
     */
    public function update(UpdateListingRequest $request, $id)
    {
        try {
            $listing = Listing::where('seeker_user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            $updated = DB::transaction(function () use ($listing, $request) {
                return $this->listingService->updateListing(
                    $listing,
                    $request->validated()
                );
            });

            Log::info('Listing updated', ['listing_id' => $listing->id, 'seeker_user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Listing updated successfully.',
                'data' => $updated->load(['tags', 'category']),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update listing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete a listing
     * DELETE /api/listings/{id}
     */
    public function destroy($id)
    {
        try {
            $listing = Listing::where('seeker_user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            DB::transaction(function () use ($listing) {
                $this->listingService->deleteListing($listing);
            });

            Log::info('Listing deleted', ['listing_id' => $listing->id, 'seeker_user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Listing deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete listing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Add a specific tag to a listing
     * POST /api/listings/{id}/tags/{tagId}
     */
    public function addTag($id, $tagId)
    {
        try {
            $listing = Listing::where('seeker_user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            $updated = DB::transaction(function () use ($listing, $tagId) {
                return $this->listingService->addTag($listing, $tagId);
            });

            Log::info('Tag added to listing', ['listing_id' => $id, 'tag_id' => $tagId, 'seeker_user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Tag added successfully.',
                'data' => $updated,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to add tag: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Remove a specific tag from a listing
     * DELETE /api/listings/{id}/tags/{tagId}
     */
    public function removeTag($id, $tagId)
    {
        try {
            $listing = Listing::where('seeker_user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            $updated = DB::transaction(function () use ($listing, $tagId) {
                return $this->listingService->removeTag($listing, $tagId);
            });

            Log::info('Tag removed from listing', ['listing_id' => $id, 'tag_id' => $tagId, 'seeker_user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Tag removed successfully.',
                'data' => $updated,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to remove tag: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }
}
