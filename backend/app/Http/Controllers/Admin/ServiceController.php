<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of services
     * GET /admin/services
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:all,pending,active,rejected,suspended',
            'category_id' => 'nullable|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $validated = $request->validated();
        $query = Service::with('provider.user', 'category');

        // Filter by status
        if (isset($validated['status']) && $validated['status'] !== 'all') {
            $query->where('status', $validated['status']);
        }

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
     * GET /admin/services/{service}
     */
    public function show(Service $service): JsonResponse
    {
        $service->load([
            'provider.user',
            'category',
            'tags',
            'reviews.user',
            'reports.user'
        ]);

        return response()->json($service, 200);
    }

    /**
     * Soft delete the specified service
     * DELETE /admin/services/{service}
     */
    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ], 200);
    }

    /**
     * Suspend a service
     * POST /admin/services/{service}/suspend
     */
    public function suspend(Request $request, Service $service): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $validated = $request->validated();

        $service->update([
            'status' => 'suspended',
            'suspension_reason' => $validated['reason'],
            'suspended_at' => now(),
            'suspended_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Service suspended successfully',
            'data' => $service
        ], 200);
    }

    /**
     * Reactivate a suspended service
     * POST /admin/services/{service}/reactivate
     */
    public function reactivate(Service $service): JsonResponse
    {
        if ($service->status === 'active') {
            return response()->json([
                'message' => 'Service is already active'
            ], 422);
        }

        $service->update([
            'status' => 'active',
            'suspension_reason' => null,
            'rejected_reason' => null,
            'suspended_at' => null,
            'suspended_by' => null,
        ]);

        return response()->json([
            'message' => 'Service reactivated successfully',
            'data' => $service
        ], 200);
    }
}
