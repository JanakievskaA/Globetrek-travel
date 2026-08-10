<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\MediaLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Backs the image picker. Everything here answers JSON — the picker is a modal
 * that talks to these three endpoints, never a page of its own.
 */
class MediaController extends Controller
{
    public function __construct(private readonly MediaLibrary $library)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('q')->trim()->value() ?: null;

        $items = $request->string('source')->value() === 'theme'
            ? $this->library->themeImages($search)
            : $this->library->uploads($search);

        return response()->json(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        // Validated by hand rather than with $request->validate(): the app only
        // renders JSON error responses for api/* (see bootstrap/app.php), and a
        // redirect to an HTML page tells the picker nothing it can show.
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'image', 'mimes:'.implode(',', MediaLibrary::EXTENSIONS), 'max:8192'],
            'alt' => ['nullable', 'string', 'max:180'],
        ], [
            'file.max' => 'Images must be 8 MB or smaller.',
            'file.image' => 'That file is not an image.',
            'file.mimes' => 'Use a JPG, PNG, WebP, SVG, GIF or AVIF file.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $media = $this->library->store($request->file('file'), $request->user()?->id, $request->input('alt'));

        return response()->json([
            'item' => [
                'id' => $media->id,
                'path' => $media->path,
                'url' => $media->url,
                'name' => $media->name,
                'meta' => $media->size_label,
                'source' => 'upload',
            ],
        ], 201);
    }

    public function destroy(Media $medium): JsonResponse
    {
        $medium->deleteFile();
        $medium->delete();

        return response()->json(['deleted' => true]);
    }
}
