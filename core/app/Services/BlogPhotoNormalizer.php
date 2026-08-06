<?php

namespace App\Services;

class BlogPhotoNormalizer
{
    /**
     * Pure function to normalize photo data.
     *
     * @param mixed $value
     * @return array
     */
    public static function normalize($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($value)) {
            return [];
        }

        $filtered = array_filter($value, function ($item) {
            return is_string($item) && trim($item) !== '';
        });

        return array_values($filtered);
    }
}
