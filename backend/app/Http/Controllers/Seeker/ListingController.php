<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Exception;

class ListingController extends Controller
{
    // ========================================================================
    // CONSTANTS
    // ========================================================================

    private const MAX_TAGS_PER_LISTING = 5;
    private const SIMILARITY_THRESHOLD = 0.3;
    private const AUTO_CORRECT_THRESHOLD = 0.7;
    private const MAX_SUGGESTIONS = 3;

    /**
     * Get seeker's own listings
     * POST /seeker/listings/search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate request body
            $validated = $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
                'status' => 'nullable|in:active,closed,expired',
                'category_id' => 'nullable|exists:categories,id',
                'search' => 'nullable|string|max:255',
                'sort_by' => 'nullable|in:created_at,updated_at,budget,title',
                'sort_order' => 'nullable|in:asc,desc',
                'has_budget' => 'nullable|boolean',
                'min_budget' => 'nullable|numeric|min:0',
                'max_budget' => 'nullable|numeric|min:0',
            ]);

            // Get own listings with filters
            $listings = $this->getOwnListings($validated);

            return response()->json([
                'success' => true,
                'data' => $listings['data'],
                'pagination' => $listings['pagination'],
                'filters_applied' => $listings['filters_applied'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to fetch seeker listings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listings.',
                'error' => config('app.debug') ?    $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Deactivate a listing (set status to expired)
     * POST /seeker/listings/{id}/deactivate
     */
    public function deactivate(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID.',
                ], 400);
            }

            $listing = $this->findListingByUser($id);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or you do not have permission to deactivate it.',
                ], 404);
            }

            if ($listing->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active listings can be deactivated.',
                ], 400);
            }

            // Set all applications to rejected
            $listing->applications()->where('status', 'pending')->update(['status' => 'rejected']);

            $listing->status = 'expired';
            $listing->save();

            Log::info('Listing deactivated (expired)', [
                'listing_id' => $listing->id,
                'seeker_user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing deactivated (set to expired) successfully. All applications have been rejected.',
                'data' => $listing,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to deactivate listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate listing.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    /**
     * Get a single listing (only owner)
     * GET /seeker/listings/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID.',
                ], 400);
            }

            $listing = $this->findListingByUser($id);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            $listing->load(['tags', 'category', 'applications']);
            $this->addIsExpired($listing);

            // Add the owner's name and Spatie roles to the response
            $listingData = $listing->toArray();
            if ($listing->seeker) {
                $listingData['owner_name'] = $listing->seeker->name;
                $listingData['owner_roles'] = $listing->seeker->getRoleNames();
            } else {
                $listingData['owner_name'] = null;
                $listingData['owner_roles'] = [];
            }

            // Add current user's role
            $user = auth()->user();
            $listingData['user_current_role'] = $user ? $user->getRoleNames()->toArray() : null;

            return response()->json([
                'success' => true,
                'data' => $listingData,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch listing.',
                'error' => config('app.debug') ?    $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Store a new listing (only owner)
     * POST /seeker/listings
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'budget' => 'nullable|numeric|min:0',
                'category_id' => 'nullable|exists:categories,id',
                'tags' => 'nullable|array|max:' . self::MAX_TAGS_PER_LISTING,
                'tags.*' => 'string|min:2|max:50',
                'expires_at' => 'nullable|date|after:now',
            ], [
                'title.required' => 'Title is required.',
                'title.max' => 'Title cannot exceed 255 characters.',
                'budget.numeric' => 'Budget must be a valid number.',
                'budget.min' => 'Budget must be at least 0.',
                'category_id.exists' => 'Selected category does not exist.',
                'tags.max' => 'You can add a maximum of ' . self::MAX_TAGS_PER_LISTING . ' tags.',
                'tags.*.min' => 'Each tag must be at least 2 characters.',
                'tags.*.max' => 'Each tag cannot exceed 50 characters.',
                'expires_at.date' => 'Expiry date must be a valid date.',
                'expires_at.after' => 'Expiry date must be in the future.',
            ]);

            $listing = DB::transaction(function () use ($validated) {
                // Extract tags
                $tags = $validated['tags'] ?? [];
                unset($validated['tags']);

                // Add authenticated user ID and default status
                $validated['seeker_user_id'] = auth()->id();
                $validated['status'] = 'active';

                // Create listing
                $listing = Listing::create($validated);

                // Handle tags
                if (!  empty($tags)) {
                    $tagIds = $this->resolveTagNames($tags);
                    $tagIds = array_slice($tagIds, 0, self::MAX_TAGS_PER_LISTING);
                    $listing->tags()->sync($tagIds);
                }

                return $listing->load(['tags', 'category']);
            });

            $this->addIsExpired($listing);

            Log::info('Listing created', [
                'listing_id' => $listing->id,
                'seeker_user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing created successfully.',
                'data' => $listing,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to create listing', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create listing.',
                'error' => config('app.debug') ?  $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update an existing listing (only owner)
     * PUT/PATCH /seeker/listings/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID.',
                ], 400);
            }

            $listing = $this->findListingByUser($id);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or you do not have permission to edit it.',
                ], 404);
            }

            // Validate request
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'budget' => 'nullable|numeric|min:0',
                'category_id' => 'nullable|exists:categories,id',
                'status' => 'sometimes|in:active,closed,expired',
                'tags' => 'nullable|array|max:' . self::MAX_TAGS_PER_LISTING,
                'tags.*' => 'string|min:2|max:50',
                'expires_at' => 'nullable|date|after:now',
            ], [
                'title.max' => 'Title cannot exceed 255 characters.',
                'budget.numeric' => 'Budget must be a valid number.',
                'budget.min' => 'Budget must be at least 0.',
                'category_id.exists' => 'Selected category does not exist.',
                'status.in' => 'Invalid status value.',
                'tags. max' => 'You can add a maximum of ' . self::MAX_TAGS_PER_LISTING . ' tags.',
                'tags.*.min' => 'Each tag must be at least 2 characters.',
                'tags. *.max' => 'Each tag cannot exceed 50 characters.',
                'expires_at.date' => 'Expiry date must be a valid date.',
                'expires_at.after' => 'Expiry date must be in the future.',
            ]);

            $updated = DB::transaction(function () use ($listing, $validated) {
                // Extract tags
                $tags = $validated['tags'] ?? null;
                unset($validated['tags']);

                // Update listing
                $listing->fill($validated);
                $listing->save();

                // Handle tags if provided
                if (is_array($tags)) {
                    $tagIds = $this->resolveTagNames($tags);
                    $tagIds = array_slice($tagIds, 0, self::MAX_TAGS_PER_LISTING);
                    $listing->tags()->sync($tagIds);
                }

                return $listing->load(['tags', 'category']);
            });

            $this->addIsExpired($updated);

            Log::info('Listing updated', [
                'listing_id' => $listing->id,
                'seeker_user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing updated successfully.',
                'data' => $updated,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to update listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update listing.',
                'error' => config('app.debug') ?  $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete a listing (only owner)
     * DELETE /seeker/listings/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID.',
                ], 400);
            }

            $listing = $this->findListingByUser($id);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found or you do not have permission to delete it.',
                ], 404);
            }

            $listing->delete();

            Log::info('Listing deleted', [
                'listing_id' => $listing->id,
                'seeker_user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Listing deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete listing', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id(),
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
     * POST /seeker/listings/{id}/tags
     */
    public function addTag(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID.',
                ], 400);
            }

            // Validate request body
            $validated = $request->validate([
                'tag' => 'required_without:tag_id|string|min:2|max:50',
                'tag_id' => 'required_without:tag|integer|exists:tags,id',
                'force_create' => 'nullable|boolean',
            ], [
                'tag.required_without' => 'Either tag name or tag_id is required.',
                'tag_id.required_without' => 'Either tag name or tag_id is required.',
                'tag. min' => 'Tag name must be at least 2 characters.',
                'tag.max' => 'Tag name cannot exceed 50 characters.',
                'tag_id.exists' => 'Tag ID does not exist.',
            ]);

            $listing = $this->findListingByUser($id);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            // Check tag limit
            if ($listing->tags()->count() >= self::MAX_TAGS_PER_LISTING) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add more tags.   Maximum of ' . self::MAX_TAGS_PER_LISTING . ' tags allowed per listing.',
                ], 422);
            }

            // Resolve tag
            if (isset($validated['tag_id'])) {
                $tag = Tag::find($validated['tag_id']);
            } else {
                $forceCreate = $validated['force_create'] ?? false;
                $tag = $this->resolveTag($validated['tag'], $forceCreate);
            }

            if (!$tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag not found or is invalid.',
                ], 404);
            }

            // Check if tag is already attached
            if ($listing->tags()->where('tags.id', $tag->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag already attached to listing.',
                ], 409);
            }

            $listing->tags()->attach($tag->id);
            $listing->load(['tags', 'category']);
            $this->addIsExpired($listing);

            return response()->json([
                'success' => true,
                'message' => 'Tag added successfully.',
                'data' => $listing,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to add tag', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id(),
            ]);

            // Check if it's a similarity suggestion error
            if (str_contains($e->getMessage(), 'Did you mean')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'suggestions' => $this->extractSuggestions($e->getMessage()),
                ], 422);
            }

            $statusCode = str_contains($e->getMessage(), 'deleted') ? 410 : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], $statusCode);
        }
    }

    /**
     * Remove tag from listing (only owner)
     * DELETE /seeker/listings/{id}/tags
     */
    public function removeTag(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid listing ID.',
                ], 400);
            }

            // Validate request body
            $validated = $request->validate([
                'tag' => 'required_without:tag_id|string|min:2|max:50',
                'tag_id' => 'required_without:tag|integer|exists:tags,id',
            ], [
                'tag.required_without' => 'Either tag name or tag_id is required.',
                'tag_id.required_without' => 'Either tag name or tag_id is required.',
                'tag.min' => 'Tag name must be at least 2 characters.',
                'tag.max' => 'Tag name cannot exceed 50 characters.',
                'tag_id.exists' => 'Tag ID does not exist.',
            ]);

            $listing = $this->findListingByUser($id);

            if (!$listing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing not found.',
                ], 404);
            }

            // Resolve tag (case-insensitive)
            if (isset($validated['tag_id'])) {
                $tag = Tag::find($validated['tag_id']);
            } else {
                $tagName = trim($validated['tag']);
                $tag = Tag::whereRaw('LOWER(name) = LOWER(?)', [$tagName])->first();
            }

            if (!$tag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag not found.',
                ], 404);
            }

            // Check if tag is attached
            if (! $listing->tags()->where('tags.id', $tag->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tag not attached to listing.',
                ], 404);
            }

            $listing->tags()->detach($tag->id);
            $listing->load(['tags', 'category']);
            $this->addIsExpired($listing);

            return response()->json([
                'success' => true,
                'message' => 'Tag removed successfully.',
                'data' => $listing,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to remove tag', [
                'error' => $e->getMessage(),
                'listing_id' => $id,
                'user_id' => auth()->id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove tag.',
                'error' => config('app.debug') ?   $e->getMessage() : null,
            ], 500);
        }
    }

    // ========================================================================
    // PRIVATE HELPER METHODS
    // ========================================================================

    /**
     * Get seeker's own listings with filters
     */
    private function getOwnListings(array $filters = []): array
    {
        $query = Listing::where('seeker_user_id', auth()->id())
            ->with(['tags', 'category']);

        return $this->applyFilters($query, $filters);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, array $filters): array
    {
        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Category filter
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Search filter
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Budget filters
        if (isset($filters['has_budget'])) {
            if ($filters['has_budget']) {
                $query->whereNotNull('budget');
            } else {
                $query->whereNull('budget');
            }
        }

        if (isset($filters['min_budget'])) {
            $query->where('budget', '>=', $filters['min_budget']);
        }

        if (isset($filters['max_budget'])) {
            $query->where('budget', '<=', $filters['max_budget']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($filters['per_page'] ?? 15, 100);
        $page = $filters['page'] ?? 1;

        $listings = $query->paginate($perPage, ['*'], 'page', $page);

        // Add is_expired to each listing
        $listings->getCollection()->transform(function ($listing) {
            return $this->addIsExpired($listing);
        });

        return [
            'data' => $listings->items(),
            'pagination' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
                'from' => $listings->firstItem(),
                'to' => $listings->lastItem(),
            ],
            'filters_applied' => [
                'status' => $filters['status'] ?? null,
                'category_id' => $filters['category_id'] ?? null,
                'search' => $filters['search'] ?? null,
                'has_budget' => $filters['has_budget'] ?? null,
                'min_budget' => $filters['min_budget'] ?? null,
                'max_budget' => $filters['max_budget'] ??   null,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ];
    }

    /**
     * Find listing by ID and ensure ownership
     */
    private function findListingByUser(int $id): ?Listing
    {
        return Listing::where('seeker_user_id', auth()->id())
            ->where('id', $id)
            ->first();
    }

    /**
     * Add is_expired property to listing
     */
    private function addIsExpired(Listing $listing): Listing
    {
        $listing->is_expired = $listing->expires_at && $listing->expires_at->isPast();
        return $listing;
    }

    /**
     * Resolve tag by name with PostgreSQL similarity detection
     *
     * @param string $tagName
     * @param bool $forceCreate
     * @return Tag|null
     * @throws Exception
     */
    private function resolveTag(string $tagName, bool $forceCreate = false): ?Tag
    {
        $tagName = trim($tagName);
        if ($tagName === '') {
            return null;
        }

        // Check for soft-deleted tags (case-insensitive)
        $existingTag = Tag::withTrashed()
            ->whereRaw('LOWER(name) = LOWER(?)', [$tagName])
            ->first();

        if ($existingTag && $existingTag->trashed()) {
            throw new Exception('This tag has been deleted and cannot be used.');
        }

        // Find exact match (case-insensitive)
        $tag = Tag::whereRaw('LOWER(name) = LOWER(? )', [$tagName])->first();

        if ($tag) {
            return $tag;
        }

        // If force create, skip similarity check
        if ($forceCreate) {
            return Tag::create(['name' => $tagName]);
        }

        // Find similar tags using PostgreSQL trigram
        $similar = $this->findSimilarTagsPg($tagName);

        if (! empty($similar)) {
            $topMatch = $similar[0];

            // Auto-correct if very similar (>70%)
            if ($topMatch['similarity'] >= self::AUTO_CORRECT_THRESHOLD) {
                return Tag::find($topMatch['id']);
            }

            // Suggest alternatives
            $suggestions = array_slice(array_column($similar, 'name'), 0, self::MAX_SUGGESTIONS);
            throw new Exception(
                'Tag "' . $tagName . '" not found. Did you mean: ' .
                implode(', ', $suggestions) . '?  ' .
                'Add "force_create": true to create this tag anyway.'
            );
        }

        // No similar tags found, create new
        return Tag::create(['name' => $tagName]);
    }

    /**
     * Find similar tags using PostgreSQL trigram similarity
     *
     * @param string $tagName
     * @param float|null $threshold
     * @return array
     */
    private function findSimilarTagsPg(string $tagName, ?float $threshold = null): array
    {
        $threshold = $threshold ?? self::SIMILARITY_THRESHOLD;

        // Cache key
        $cacheKey = 'similar_tags_pg_' . md5(strtolower($tagName)) . '_' . $threshold;

        return Cache::remember($cacheKey, 3600, function () use ($tagName, $threshold) {
            // Use PostgreSQL similarity function
            $results = DB::select("
                SELECT
                    id,
                    name,
                    similarity(LOWER(name), LOWER(?)) as similarity,
                    ROUND(similarity(LOWER(name), LOWER(? ))::numeric * 100, 2) as similarity_percent
                FROM tags
                WHERE
                    similarity(LOWER(name), LOWER(? )) > ?
                    AND deleted_at IS NULL
                ORDER BY similarity DESC
                LIMIT ?
            ", [$tagName, $tagName, $tagName, $threshold, self::MAX_SUGGESTIONS * 2]);

            return array_map(function ($result) {
                return [
                    'id' => $result->id,
                    'name' => $result->name,
                    'similarity' => (float) $result->similarity,
                    'similarity_percent' => (float) $result->similarity_percent,
                ];
            }, $results);
        });
    }

    /**
     * Resolve array of tag names with PostgreSQL similarity
     *
     * @param array $tagNames
     * @return array
     */
    private function resolveTagNames(array $tagNames): array
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $trimmed = trim($tagName);
            if ($trimmed === '') {
                continue;
            }

            // Check for soft-deleted
            $existingTag = Tag::withTrashed()
                ->whereRaw('LOWER(name) = LOWER(?)', [$trimmed])
                ->first();

            if ($existingTag && $existingTag->trashed()) {
                continue;
            }

            // Find exact match (case-insensitive)
            $tag = Tag::whereRaw('LOWER(name) = LOWER(?)', [$trimmed])->first();

            if (!  $tag) {
                // Check for very similar tags (>70% similar)
                $similar = $this->findSimilarTagsPg($trimmed);

                if (!empty($similar) && $similar[0]['similarity'] >= self::AUTO_CORRECT_THRESHOLD) {
                    // Auto-use very similar tag
                    $tag = Tag::find($similar[0]['id']);
                } else {
                    // Create new tag
                    $tag = Tag::create(['name' => $trimmed]);
                }
            }

            $tagIds[] = $tag->id;
        }

        return array_unique($tagIds);
    }

    /**
     * Extract suggestions from error message
     */
    private function extractSuggestions(string $message): array
    {
        if (preg_match('/Did you mean: (.*? )\?/', $message, $matches)) {
            return array_map('trim', explode(',', $matches[1]));
        }
        return [];
    }
}
