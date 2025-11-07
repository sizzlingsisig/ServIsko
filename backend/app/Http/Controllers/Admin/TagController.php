<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // List all tags (including soft-deleted for admin)
    public function index()
    {
        $tags = Tag::withTrashed()->orderBy('name')->get();
        return response()->json($tags);
    }

    // Create a new tag
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);
        $tag = Tag::create($validated);
        return response()->json($tag, 201);
    }

    // Show a tag (even soft deleted ones)
    public function show($id)
    {
        $tag = Tag::withTrashed()->findOrFail($id);
        return response()->json($tag);
    }

    // Update a tag
    public function update(Request $request, $id)
    {
        $tag = Tag::withTrashed()->findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);
        $tag->update($validated);
        return response()->json($tag);
    }

    // Soft delete a tag
    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();
        return response()->json(['message' => 'Tag deleted.']);
    }

    // Restore a soft-deleted tag (optional for admin)
    public function restore($id)
    {
        $tag = Tag::withTrashed()->findOrFail($id);
        $tag->restore();
        return response()->json(['message' => 'Tag restored.']);
    }
}
