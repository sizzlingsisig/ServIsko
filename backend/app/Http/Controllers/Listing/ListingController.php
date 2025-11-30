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
use Illuminate\Http\JsonResponse;
use Exception;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService
    ) {}

    /**
     * Get ALL active listings (public - for browsing)
     * GET /api/listings
     */
    public function index(FilterListingRequest $request): JsonResponse
    {
        try {
            $listings = $this->listingService->getAllListings(
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => $listings,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch all listings', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get authenticated user's OWN listings
     * GET /api/my-listings OR /api/listings/my
     */
    public function myListings(FilterListingRequest $request): JsonResponse
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
            Log::error('Failed to fetch user listings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your listings.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show a specific listing (public - anyone can view)
     * GET /api/listings/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $listing = Listing::with(['seeker', 'category', 'tags', 'hiredUser'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $listing,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Listing not found.',
            ], 404);
        }
    }

    /**
     * Create a new listing
     * POST /api/listings
     */
    public function store(StoreListingRequest $request): JsonResponse
    {
        try {
            $listing = DB::transaction(function () use ($request) {
                return $this->listingService->createListing(
                    $request->validated(),
                    auth()->id()
                );
            });

            Log::info('Listing created', [
                'listing_id' => $listing->id,
                'seeker_user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing created successfully.',
                'data' => $listing->load(['tags', 'category']),
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create listing', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update listing details (only owner can update)
     * PUT/PATCH /api/listings/{id}
     */
    public function update(UpdateListingRequest $request, $id): JsonResponse
    {
        try {
            $listing = Listing::where('seeker_user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or you do not have permission to edit it.',
                ], 404);
            }

            $updated = DB::transaction(function () use ($listing, $request) {
                return $this->listingService->updateListing(
                    $listing,
                    $request->validated()
                );
            });

            Log::info('Listing updated', [
                'listing_id' => $listing->id,
                'seeker_user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing updated successfully.',
                'data' => $updated->load(['tags', 'category']),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete a listing (only owner can delete)
     * DELETE /api/listings/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $listing = Listing::where('seeker_user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or you do not have permission to delete it.',
                ], 404);
            }

            $this->listingService->deleteListing($listing);

            Log::info('Listing deleted', [
                'listing_id' => $listing->id,
                'seeker_user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Add tag to listing (only owner)
     * POST /api/listings/{id}/tags/{tagId}
     */
    public function addTag($id, $tagId): JsonResponse
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

            $updated = $this->listingService->addTag($listing, $tagId);

            return response()->json([
                'success' => true,
                'message' => 'Tag added successfully.',
                'data' => $updated,
            ]);
        } catch (Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 409;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Remove tag from listing (only owner)
     * DELETE /api/listings/{id}/tags/{tagId}
     */
    public function removeTag($id, $tagId): JsonResponse
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

            $updated = $this->listingService->removeTag($listing, $tagId);

            return response()->json([
                'success' => true,
                'message' => 'Tag removed successfully.',
                'data' => $updated,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
