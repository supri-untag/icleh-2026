<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SpeakerPhotoController extends Controller
{
    /**
     * Serve public speaker photos through Laravel when the storage symlink is unavailable.
     */
    public function __invoke(string $filename): BinaryFileResponse
    {
        abort_if(
            blank($filename)
                || basename($filename) !== $filename
                || Str::contains($filename, ['/', '\\', "\0"]),
            404
        );

        $extension = Str::lower(pathinfo($filename, PATHINFO_EXTENSION));

        abort_unless(in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true), 404);

        $relativePath = 'speakers/'.$filename;

        abort_unless(Storage::disk('public')->exists($relativePath), 404);

        return response()->file(Storage::disk('public')->path($relativePath), [
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
