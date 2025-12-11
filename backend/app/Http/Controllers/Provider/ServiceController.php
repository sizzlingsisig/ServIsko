<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of the provider's services
     * GET /provider/services
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $validated = $request->only(['category_id', 'search', 'per_page']);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        // Get authenticated provider's services
        $query = $user->services()
            ->with('category', 'tags');

        // Filter by category
        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        // Search
        if (isset($validated['search'])) {
            $query->where(function($q) use ($validated) {
                $q->where('title', 'like', "%{$validated['search']}%")
                  ->orWhere('description', 'like', "%{$validated['search']}%");
            });
        }

        $perPage = $validated['per_page'] ?? 20;
        $services = $query->latest()->paginate($perPage);

        return response()->json($services, 200);
    }

    /**
     * Display the specified service
     * GET /provider/services/{service}
     */
    public function show(Service $service): JsonResponse
    {
        // Ensure provider can only view their own services
        if ($service->provider_id !== auth()->user()->provider->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $service->load('category', 'tags');

        return response()->json($service, 200);
    }

    /**
     * Store a newly created service
     * POST /provider/services
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'tags' => 'nullable|array|max:5',
            'tags.*' => 'string|min:2|max:50',
        ], [
            'tags.max' => 'You can add a maximum of 5 tags.',
            'tags.*.min' => 'Each tag must be at least 2 characters.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
        ]);

        $validated = $request->only(['category_id', 'title', 'description', 'price', 'tags']);

        $service = auth()->user()->services()->create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'] ?? null,
        ]);

        // Handle tags (robust, like ListingController)
        if (!empty($validated['tags'])) {
            $tagIds = $this->resolveTagNames($validated['tags']);
            $tagIds = array_slice($tagIds, 0, 5);
            $service->tags()->sync($tagIds);
        }

        $service->load('category', 'tags');

        return response()->json([
            'message' => 'Service created successfully',
            'data' => $service
        ], 201);
    }

    /**
     * Update the specified service
     * PUT/PATCH /provider/services/{service}
     */
    public function update(Request $request, Service $service): JsonResponse
    {

        $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:5000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'tags' => 'nullable|array|max:5',
            'tags.*' => 'string|min:2|max:50',
        ], [
            'tags.max' => 'You can add a maximum of 5 tags.',
            'tags.*.min' => 'Each tag must be at least 2 characters.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
        ]);
        $validated = $request->all();
        $tags = $validated['tags'] ?? null;
        unset($validated['tags']);
        $service->update($validated);
        // Handle tags (robust, like ListingController)
        if (is_array($tags)) {
            $tagIds = $this->resolveTagNames($tags);
            $tagIds = array_slice($tagIds, 0, 5);
            $service->tags()->sync($tagIds);
        }
        return response()->json([
            'message' => 'Service updated successfully',
            'data' => $service->fresh(['category', 'tags'])
        ], 200);
    }

    /**
     * Remove the specified service
     * DELETE /provider/services/{service}
     */
    public function destroy(Service $service): JsonResponse
    {
        $service->delete();
        return response()->json([
            'message' => 'Service deleted successfully'
        ], 200);
    }

    /**
     * Add tags to a service (accepts tag IDs or names, auto-creates if needed, max 5 tags)
     * POST /provider/services/{service}/tags
     */
    public function addTags(Request $request, Service $service): JsonResponse
    {
        // Ensure provider can only modify their own services
        if ($service->provider_id !== auth()->user()->provider->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'tags' => 'required|array|min:1|max:5',
            'tags.*' => 'string|min:2|max:50',
        ]);

        $tagInputs = $request->input('tags', []);
        $tagIds = [];
        foreach ($tagInputs as $tagInput) {
            // If numeric, treat as tag ID
            if (is_numeric($tagInput)) {
                $tag = \App\Models\Tag::find($tagInput);
                if ($tag) {
                    $tagIds[] = $tag->id;
                }
                continue;
            }
            // Otherwise, treat as tag name (case-insensitive)
            $tagName = trim($tagInput);
            if ($tagName === '') continue;
            $existingTag = \App\Models\Tag::whereRaw('LOWER(name) = LOWER(?)', [$tagName])->first();
            if ($existingTag) {
                $tagIds[] = $existingTag->id;
            } else {
                $newTag = \App\Models\Tag::create(['name' => $tagName]);
                $tagIds[] = $newTag->id;
            }
        }
        // Limit to 5 tags per service
        $currentTagIds = $service->tags()->pluck('tags.id')->toArray();
        $allTagIds = array_unique(array_merge($currentTagIds, $tagIds));
        if (count($allTagIds) > 5) {
            return response()->json([
                'message' => 'Cannot add more than 5 tags to a service.'
            ], 422);
        }
        // Attach tags without removing existing ones
        $service->tags()->syncWithoutDetaching($tagIds);

        return response()->json([
            'message' => 'Tags added successfully',
            'data' => $service->load('tags')
        ], 200);
    }

    /**
     * Remove tags from a service
     * DELETE /provider/services/{service}/tags
     */
    public function removeTags(Request $request, Service $service): JsonResponse
    {
        // Ensure provider can only modify their own services
        if ($service->provider_id !== auth()->user()->provider->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|exists:tags,id',
        ]);

        $validated = $request->validated();

        // Detach specific tags
        $service->tags()->detach($validated['tags']);

        return response()->json([
            'message' => 'Tags removed successfully',
            'data' => $service->load('tags')
        ], 200);
    }

    /**
     * Helper: Resolve array of tag names with similarity and deduplication (like ListingController)
     * @param array $tagNames
     * @return array
     */
    private function resolveTagNames(array $tagNames): array
    {
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $trimmed = trim($tagName);
            if ($trimmed === '') continue;
            // Check for soft-deleted
            $existingTag = \App\Models\Tag::withTrashed()
                ->whereRaw('LOWER(name) = LOWER(?)', [$trimmed])
                ->first();
            if ($existingTag && $existingTag->trashed()) continue;
            // Find exact match (case-insensitive)
            $tag = \App\Models\Tag::whereRaw('LOWER(name) = LOWER(?)', [$trimmed])->first();
            if (! $tag) {
                // Optionally: add similarity check here if you want (see ListingController)
                $tag = \App\Models\Tag::create(['name' => $trimmed]);
            }
            $tagIds[] = $tag->id;
        }
        return array_unique($tagIds);
    }
}
