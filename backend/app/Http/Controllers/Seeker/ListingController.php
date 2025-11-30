<?php

namespace App\Http\Controllers\Seeker;

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
     * Get seeker's own listings
     * GET /seeker/listings
     */
    public function index(FilterListingRequest $request): JsonResponse
    {
        try {
            $listings = $this->listingService->getUserListings(auth()->id(), $request->validated());
            return response()->json(['success' => true, 'data' => $listings]);
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
     * Create a new listing
     * POST /seeker/listings
     */
    public function store(StoreListingRequest $request): JsonResponse
    {
        try {
            $listing = DB::transaction(fn() =>
                $this->listingService->createListing($request->validated(), auth()->id())
            );
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
     * Update an existing listing (only owner)
     * PUT/PATCH /seeker/listings/{id}
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

            $updated = DB::transaction(fn() =>
                $this->listingService->updateListing($listing, $request->validated())
            );

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
     * Delete a listing (only owner)
     * DELETE /seeker/listings/{id}
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
     * POST /seeker/listings/{id}/tags/{tagId}
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
            Log::error('Failed to add tag', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'tag_id' => $tagId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Remove tag from listing (only owner)
     * DELETE /seeker/listings/{id}/tags/{tagId}
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
            Log::error('Failed to remove tag', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'tag_id' => $tagId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }
}
