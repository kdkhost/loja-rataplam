<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BlogFileService
{
    /**
     * Check if a filename is safe (no path traversal, no URLs, no null bytes).
     *
     * @param string|null $filename
     * @return bool
     */
    public static function isSafeFilename(?string $filename): bool
    {
        if (empty($filename) || trim($filename) === '') {
            return false;
        }

        if (
            str_contains($filename, '..') ||
            str_contains($filename, '\\') ||
            str_contains($filename, '/') ||
            str_contains($filename, "\0") ||
            filter_var($filename, FILTER_VALIDATE_URL)
        ) {
            return false;
        }

        return basename($filename) === $filename;
    }

    /**
     * Delete a blog image safely using Storage::disk('public').
     *
     * Reuses ImageHelper::deletePublicMirror pattern:
     * - Storage files live in storage/app/public/images/
     * - Public mirror is symlinked at public/storage/images/
     * Since public/storage → storage/app/public is a symlink,
     * deleting via Storage::disk('public') removes both.
     *
     * @param string|null $filename
     * @return bool
     */
    public static function deleteImage(?string $filename): bool
    {
        if (empty($filename) || trim($filename) === '') {
            return true; // Idempotent: nothing to do
        }

        if (!self::isSafeFilename($filename)) {
            Log::warning('Blog: tentativa de exclusão de arquivo com nome inválido bloqueada.');
            return false;
        }

        $disk = Storage::disk('public');
        $relativePath = 'images/' . $filename;

        try {
            if (!$disk->exists($relativePath)) {
                return true; // Idempotent: file already gone
            }

            $deleted = $disk->delete($relativePath);

            if (!$deleted) {
                Log::warning('Blog: Storage::delete retornou false para arquivo de imagem.');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::warning('Blog: falha ao excluir arquivo de imagem: ' . $e->getMessage());
            return false;
        }
    }
}
