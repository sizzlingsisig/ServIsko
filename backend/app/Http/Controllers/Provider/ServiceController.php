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

        $validated = $request->validated();

        // Get authenticated provider's services
        $query = auth()->user()->provider->services()
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
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $validated = $request->validated();

        // Create service for authenticated provider
        $service = auth()->user()->provider->services()->create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'] ?? null,
        ]);

        // Attach tags if provided
        if (isset($validated['tags'])) {
            $service->tags()->attach($validated['tags']);
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
        // Ensure provider can only update their own services
        if ($service->provider_id !== auth()->user()->provider->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Prevent editing suspended services
        if ($service->suspended_at) {
            return response()->json([
                'message' => 'Cannot edit suspended service'
            ], 422);
        }

        $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:5000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $validated = $request->validated();
        $service->update($validated);

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
        // Ensure provider can only delete their own services
        if ($service->provider_id !== auth()->user()->provider->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ], 200);
    }

    /**
     * Add tags to a service
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
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|exists:tags,id',
        ]);

        $validated = $request->validated();

        // Attach tags without removing existing ones
        $service->tags()->syncWithoutDetaching($validated['tags']);

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
}
