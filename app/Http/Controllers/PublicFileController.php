<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicFileController extends Controller
{
    public function show(string $path): BinaryFileResponse|Response
    {
        $relativePath = ltrim(str_replace('\\', '/', $path), '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            abort(404);
        }

        $fullPath = public_storage_path($relativePath);

        if (!is_file($fullPath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        return response()->file($fullPath, [
            'Content-Type' => mime_type_from_extension($extension),
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
