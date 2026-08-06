<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Str;

class BlogSlugService
{
    /**
     * Generate a unique slug for a Post.
     *
     * @param string $title
     * @param int|null $ignoreId Post ID to ignore when checking for collisions (used on update)
     * @param callable|null $existsResolver A custom callback to resolve if a slug exists (useful for pure unit testing)
     * @return string
     * @throws \InvalidArgumentException when slug cannot be generated
     */
    public static function generateSlug(string $title, ?int $ignoreId = null, ?callable $existsResolver = null): string
    {
        $baseSlug = Str::slug($title);

        if (empty($baseSlug)) {
            throw new \InvalidArgumentException(
                'Não foi possível gerar um slug válido a partir do título fornecido.'
            );
        }

        $slug = $baseSlug;
        $counter = 2;

        $resolver = $existsResolver ?: function (string $s, ?int $id) {
            return self::slugExists($s, $id);
        };

        while ($resolver($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug already exists in the database.
     *
     * @param string $slug
     * @param int|null $ignoreId
     * @return bool
     */
    protected static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Post::where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
