<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicProviderController extends Controller
{
    /**
     * List all users who have the "service-provider" role, including their ProviderProfile, links, skills, and SERVICES, with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with([
            'providerProfile.links',
            'providerProfile.skills',
            'profile',
            'services',
        ])
        ->role('service-provider')
        ->whereHas('providerProfile');

        // --- FILTERS ---

        // Filter by provider name (search, e.g. ?search=John)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'ilike', "%{$search}%");
        }

        // Filter by location (e.g. ?location=Makati)
        if ($request->filled('location')) {
            $location = $request->input('location');
            $query->whereHas('providerProfile', function($q) use ($location) {
                $q->where('location', 'ilike', "%{$location}%"); // assuming provider_profiles table has 'location'
            });
        }

        // Filter by skill (e.g. ?skill_id=5 or ?skill[]=6&skill[]=7)
        if ($request->has('skill_id')) {
            $skillId = $request->input('skill_id');
            $query->whereHas('providerProfile.skills', function($q) use ($skillId) {
                $q->where('skills.id', $skillId);
            });
        } elseif ($request->has('skill')) {
            $skills = $request->input('skill');
            $skills = is_array($skills) ? $skills : [$skills];
            $query->whereHas('providerProfile.skills', function($q) use ($skills) {
                $q->whereIn('skills.id', $skills);
            });
        }

        // Filter by category of services offered (e.g. ?category_id=3)
        if ($request->filled('category_id')) {
            $catId = $request->input('category_id');
            $query->whereHas('services', function($q) use ($catId) {
                $q->where('category_id', $catId);
            });
        }

        // Filter min/max price in services offered (e.g. ?min_price=300&max_price=1200)
        if ($request->filled('min_price')) {
            $minPrice = $request->input('min_price');
            $query->whereHas('services', function($q) use ($minPrice) {
                $q->where('price', '>=', $minPrice);
            });
        }
        if ($request->filled('max_price')) {
            $maxPrice = $request->input('max_price');
            $query->whereHas('services', function($q) use ($maxPrice) {
                $q->where('price', '<=', $maxPrice);
            });
        }

        // --- SORTING ---
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $request->input('per_page', 15);

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Show a user with the "service-provider" role, with their ProviderProfile, links, skills, and SERVICES.
     */
    public function show($id): JsonResponse
    {
        $user = User::with([
                'providerProfile.links',
                'providerProfile.skills',
                'profile',
                'services',
            ])
            ->role('service-provider')
            ->whereHas('providerProfile')
            ->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Service provider not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}
