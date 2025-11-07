<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Listing\FilterListingRequest;
use App\Services\Admin\ListingService;
use Illuminate\Http\Request;
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
     * Get all listings with optional filters (admin only)
     * GET /admin/listings?status=active&category_id=1&per_page=20
     */
    public function index(FilterListingRequest $request)
    {
        try {
            $listings = $this->listingService->getListings(
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
     * Show a specific listing (admin only)
     * GET /admin/listings/{listingId}
     */
    public function show($listingId)
    {
        try {
            $listing = $this->listingService->getListing($listingId);

            return response()->json([
                'success' => true,
                'data' => $listing,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch listing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }

    /**
     * Get listing statistics (admin only)
     * GET /admin/listings/stats
     */
    public function stats()
    {
        try {
            $stats = $this->listingService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch listing stats: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete a listing (soft delete) (admin only)
     * DELETE /admin/listings/{listingId}
     */
    public function destroy($listingId)
    {
        try {
            DB::transaction(function () use ($listingId) {
                $this->listingService->deleteListing($listingId);
            });

            Log::info('Listing deleted by admin', [
                'listing_id' => $listingId,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete listing: ' . $e->getMessage());

            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }


    /**
     * Restore a soft-deleted listing (admin only)
     * POST /admin/listings/{listingId}/restore
     */
    public function restore($listingId)
    {
        try {
            $listing = DB::transaction(function () use ($listingId) {
                return $this->listingService->restoreListing($listingId);
            });

            Log::info('Listing restored by admin', [
                'listing_id' => $listingId,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing restored successfully.',
                'data' => $listing->load('seeker', 'category', 'tags'),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to restore listing: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 404);
        }
    }

    /**
     * Get deleted listings (admin only)
     * GET /admin/listings/deleted
     */
    public function getDeleted(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);

            $listings = $this->listingService->getDeletedListings($perPage);

            return response()->json([
                'success' => true,
                'data' => $listings,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch deleted listings: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch deleted listings.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
