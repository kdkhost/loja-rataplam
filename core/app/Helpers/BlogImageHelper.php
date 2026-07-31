<?php

namespace App\Helpers;

use App\Services\BlogFileService;
use Illuminate\Support\Facades\Storage;

class BlogImageHelper
{
    /**
     * Get the full URL for a blog image or placeholder.
     *
     * Uses the same Storage disk ('public') and path ('images/')
     * used by ImageHelper for uploads.
     *
     * @param string|array|null $photo
     * @return string
     */
    public static function url($photo): string
    {
        $filename = null;

        if (is_array($photo)) {
            $filename = $photo[0] ?? null;
        } elseif (is_string($photo) && trim($photo) !== '') {
            $filename = $photo;
        }

        if (empty($filename) || !BlogFileService::isSafeFilename($filename)) {
            return self::placeholderUrl();
        }

        $relativePath = 'images/' . $filename;

        if (!Storage::disk('public')->exists($relativePath)) {
            return self::placeholderUrl();
        }

        return Storage::disk('public')->url($relativePath);
    }

    /**
     * URL for the placeholder image.
     *
     * @return string
     */
    private static function placeholderUrl(): string
    {
        return Storage::disk('public')->url('images/placeholder.png');
    }
}
