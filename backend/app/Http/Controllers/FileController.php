<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Exception;

class FileController extends Controller
{
    /**
     * Get all files for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $files = $request->user()->fileUploads()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'original_name' => $file->original_name,
                        'mime_type' => $file->mime_type,
                        'formatted_size' => $file->formatted_size,
                        'description' => $file->description,
                        'created_at' => $file->created_at->format('M d, Y H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'files' => $files,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve files.',
            ], 500);
        }
    }

    /**
     * Upload a new file
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'description' => 'nullable|string|max:255',
            ]);

            $file = $request->file('file');
            
            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Store file in user-specific directory
            $path = $file->storeAs(
                'uploads/' . $request->user()->id,
                $filename,
                'local'
            );

            // Create database record
            $fileUpload = FileUpload::create([
                'user_id' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'path' => $path,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'file' => [
                    'id' => $fileUpload->id,
                    'original_name' => $fileUpload->original_name,
                    'mime_type' => $fileUpload->mime_type,
                    'formatted_size' => $fileUpload->formatted_size,
                    'description' => $fileUpload->description,
                    'created_at' => $fileUpload->created_at->format('M d, Y H:i'),
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Download a file
     */
    public function download(Request $request, $id)
    {
        try {
            $fileUpload = $request->user()->fileUploads()->findOrFail($id);
            
            if (!Storage::exists($fileUpload->path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found.',
                ], 404);
            }

            return Storage::download($fileUpload->path, $fileUpload->original_name);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file.',
            ], 500);
        }
    }

    /**
     * Delete a file
     */
    public function destroy(Request $request, $id)
    {
        try {
            $fileUpload = $request->user()->fileUploads()->findOrFail($id);
            
            // Delete physical file
            if (Storage::exists($fileUpload->path)) {
                Storage::delete($fileUpload->path);
            }

            // Delete database record
            $fileUpload->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully!',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file.',
            ], 500);
        }
    }
}
