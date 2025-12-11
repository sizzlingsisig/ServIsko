<?php

namespace App\Http\Controllers\Seeker;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // List all active tags (for seekers, exclude soft-deleted)
    public function index()
    {
        $tags = Tag::orderBy('name')->get();
        return response()->json(['data' => $tags]);
    }

    // Show a single tag (only if not soft-deleted)
    public function show($id)
    {
        $tag = Tag::findOrFail($id);
        return response()->json(['data' => $tag]);
    }
}
