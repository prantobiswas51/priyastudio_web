<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoLookupController extends Controller
{
    public function __invoke(Request $request): JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $sanitizedName = $this->sanitizeName($validated['name']);

        if ($sanitizedName === '') {
            return response()->json([
                'message' => 'The name field is invalid after sanitization.',
            ], 422);
        }

        $photo = Photo::query()
            ->where('name', $sanitizedName)
            ->first();

        if (! $photo) {
            $photo = Photo::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($sanitizedName)])
                ->first();
        }

        if (! $photo) {
            return response()->json([
                'message' => 'Photo not found.',
                'sanitized_name' => $sanitizedName,
            ], 404);
        }

        $relativePath = $this->normalizePublicPath((string) $photo->url);

        if (! Storage::disk('public')->exists($relativePath)) {
            return response()->json([
                'message' => 'Photo file not found on disk.',
                'path' => $relativePath,
            ], 404);
        }

        $acceptHeader = strtolower((string) $request->header('accept', ''));

        if (str_contains($acceptHeader, 'image/')) {
            return response()->file(Storage::disk('public')->path($relativePath));
        }

        $publicUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/storage/' . ltrim($relativePath, '/');

        return response()->json([
            'name' => $photo->name,
            'type' => $photo->type,
            'url' => $publicUrl,
            'path' => $relativePath,
        ]);
    }

    private function sanitizeName(string $name): string
    {
        $name = trim(strip_tags($name));

        // Keep letters, numbers, spaces, dashes and underscores.
        $name = preg_replace('/[^A-Za-z0-9 _-]/', '', $name) ?? '';

        // Collapse repeated spaces for stable matching.
        return preg_replace('/\s+/', ' ', $name) ?? '';
    }

    private function normalizePublicPath(string $path): string
    {
        return ltrim(str_replace(['storage/app/public/', 'public/'], '', $path), '/');
    }
}
