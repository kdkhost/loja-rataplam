<?php

namespace App\Helpers;

use App\Services\BlogFileService;

class BlogImageHelper
{
    /**
     * Get the full URL for a blog image or placeholder.
     *
     * Files are stored via ImageHelper::mirrorUploadedFile() in:
     *   core/public/storage/images/<filename>
     * which equals public_path('storage/images/<filename>').
     *
     * The web root of this hosting is public_html/ (not core/public/),
     * so these files are accessible at:
     *   https://host/core/public/storage/images/<filename>
     * which is what asset('core/public/storage/images/<filename>') returns.
     *
     * @param string|array|null $photo
     * @return string
     */
    public static function url($photo): string
    {
        $filename = self::extractFilename($photo);

        if (empty($filename) || !BlogFileService::isSafeFilename($filename)) {
            return self::placeholderUrl();
        }

        // Primary: public mirror path (core/public/storage/images/)
        // This is where ImageHelper::mirrorUploadedFile() saves uploaded files.
        $mirrorPath = public_path('storage/images/' . $filename);
        if (file_exists($mirrorPath)) {
            return asset('core/public/storage/images/' . $filename);
        }

        return self::placeholderUrl();
    }

    /**
     * Check if a blog post has at least one accessible image.
     *
     * @param string|array|null $photo
     * @return bool
     */
    public static function hasImage($photo): bool
    {
        $filename = self::extractFilename($photo);
        if (empty($filename) || !BlogFileService::isSafeFilename($filename)) {
            return false;
        }
        return file_exists(public_path('storage/images/' . $filename));
    }

    /**
     * Extract the first filename from a photo value (array or string).
     *
     * @param string|array|null $photo
     * @return string|null
     */
    public static function extractFilename($photo): ?string
    {
        if (is_array($photo)) {
            return $photo[0] ?? null;
        }

        if (is_string($photo) && trim($photo) !== '') {
            // Try JSON decode for legacy JSON strings like '["filename.jpg"]'
            $decoded = json_decode($photo, true);
            if (is_array($decoded) && !empty($decoded[0])) {
                return $decoded[0];
            }
            return $photo;
        }

        return null;
    }

    /**
     * URL for the placeholder image when no valid image is found.
     *
     * @return string
     */
    private static function placeholderUrl(): string
    {
        $placeholder = public_path('storage/images/placeholder.png');
        if (file_exists($placeholder)) {
            return asset('core/public/storage/images/placeholder.png');
        }

        // Generic no-image fallback
        return asset('core/public/images/no-image.png');
    }
}
