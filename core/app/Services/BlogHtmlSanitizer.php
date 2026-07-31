<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use HTMLPurifier;
use HTMLPurifier_Config;

class BlogHtmlSanitizer
{
    protected static ?HTMLPurifier $purifier = null;

    public static function sanitize($html): string
    {
        if (empty($html) || !is_string($html)) {
            return '';
        }

        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();

            $config->set('HTML.Allowed',
                'p,br,strong,b,em,i,u,' .
                'ul,ol,li,' .
                'h1,h2,h3,h4,h5,h6,' .
                'blockquote,' .
                'a[href|title|target|rel],' .
                'img[src|alt|title|width|height],' .
                'table,thead,tbody,tfoot,tr,th,td,' .
                'hr,span,div'
            );

            $config->set('HTML.TargetBlank', true);
            $config->set('URI.AllowedSchemes', [
                'http' => true,
                'https' => true,
                'mailto' => true,
            ]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('Attr.AllowedRel', ['nofollow', 'noopener', 'noreferrer', 'author', 'help', 'alternate']);

            $cacheDir = storage_path('app/purifier');
            if (!file_exists($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }
            $config->set('Cache.SerializerPath', $cacheDir);

            self::$purifier = new HTMLPurifier($config);
        }

        $sanitized = self::$purifier->purify($html);

        return self::normalizeBlankTargetRel($sanitized);
    }

    /**
     * Use DOMDocument to add noopener noreferrer to target="_blank" links,
     * preserving existing rel values without duplication.
     */
    private static function normalizeBlankTargetRel(string $html): string
    {
        if (empty($html) || !str_contains($html, 'target')) {
            return $html;
        }

        $doc = new DOMDocument();

        // Suppress warnings for HTML fragments
        $wrapped = '<html><body>' . $html . '</body></html>';
        @$doc->loadHTML(
            '<?xml encoding="UTF-8">' . $wrapped,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $xpath = new DOMXPath($doc);
        $links = $xpath->query('//a[@target="_blank"]');

        foreach ($links as $link) {
            $existingRel = $link->getAttribute('rel');
            $relTokens = $existingRel ? preg_split('/\s+/', trim($existingRel)) : [];

            $required = ['noopener', 'noreferrer'];
            foreach ($required as $token) {
                if (!in_array($token, $relTokens, true)) {
                    $relTokens[] = $token;
                }
            }

            $link->setAttribute('rel', implode(' ', $relTokens));
        }

        // Extract content from body
        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) {
            return $html;
        }

        $output = '';
        foreach ($body->childNodes as $child) {
            $output .= $doc->saveHTML($child);
        }

        return $output;
    }
}
