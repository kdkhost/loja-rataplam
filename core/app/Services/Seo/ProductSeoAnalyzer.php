<?php

namespace App\Services\Seo;

use App\Helpers\PriceHelper;
use App\Models\Item;
use Illuminate\Support\Str;

class ProductSeoAnalyzer
{
    public function analyzeFromArray(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? Str::slug($name)));
        $title = trim((string) ($data['seo_title'] ?? '')) ?: $name;
        $description = trim((string) ($data['meta_description'] ?? '')) ?: trim(strip_tags((string) ($data['sort_details'] ?? '')));
        $focusKeyword = trim((string) ($data['seo_focus_keyword'] ?? ''));
        $keywords = $this->normalizeKeywords((string) ($data['meta_keywords'] ?? ''));
        $detailsText = trim(strip_tags((string) ($data['details'] ?? '')));
        $robots = trim((string) ($data['seo_robots'] ?? 'index,follow')) ?: 'index,follow';
        $canonical = trim((string) ($data['seo_canonical_url'] ?? ''));
        $hasImage = !empty($data['og_image'] ?? null) || !empty($data['twitter_image'] ?? null) || !empty($data['photo'] ?? null);

        $checks = [
            $this->check('Titulo SEO preenchido', $title !== '', 10, 'Defina um titulo SEO claro para o produto.'),
            $this->check('Titulo entre 35 e 70 caracteres', $this->between(Str::length($title), 35, 70), 12, 'Ajuste o titulo para ficar entre 35 e 70 caracteres.'),
            $this->check('Descricao SEO preenchida', $description !== '', 10, 'Preencha a meta description.'),
            $this->check('Descricao entre 120 e 160 caracteres', $this->between(Str::length($description), 120, 160), 16, 'A meta description ideal fica entre 120 e 160 caracteres.'),
            $this->check('Palavra-chave foco definida', $focusKeyword !== '', 10, 'Informe uma palavra-chave foco para orientar a pagina.'),
            $this->check('Palavra-chave no titulo', $focusKeyword !== '' && Str::contains(Str::lower($title), Str::lower($focusKeyword)), 8, 'Inclua a palavra-chave foco no titulo SEO.'),
            $this->check('Palavra-chave na descricao', $focusKeyword !== '' && Str::contains(Str::lower($description), Str::lower($focusKeyword)), 8, 'Inclua a palavra-chave foco na meta description.'),
            $this->check('Slug amigavel', $slug !== '' && preg_match('/^[a-z0-9-]+$/', $slug), 6, 'Use um slug limpo, sem espacos ou caracteres especiais.'),
            $this->check('Conteudo descritivo consistente', Str::length($detailsText) >= 250, 8, 'A descricao completa deve ter pelo menos 250 caracteres.'),
            $this->check('Imagem social disponivel', $hasImage, 6, 'Use uma imagem principal ou imagem social para compartilhamento.'),
            $this->check('Canonical configurado ou automatico', $canonical !== '' || $slug !== '', 4, 'Defina uma URL canonica ou mantenha o slug correto.'),
            $this->check('Robots indexavel', !Str::contains(Str::lower($robots), 'noindex'), 4, 'Evite noindex em produtos publicados.'),
            $this->check('Palavras-chave suficientes', count($keywords) >= 3, 6, 'Informe ao menos 3 palavras-chave relevantes.'),
        ];

        $score = collect($checks)->sum(function ($check) {
            return $check['passed'] ? $check['points'] : 0;
        });

        return [
            'score' => min(100, (int) $score),
            'checks' => $checks,
            'preview' => [
                'title' => $title,
                'description' => $description,
                'url' => $canonical ?: ($slug ? url('/product/' . $slug) : url('/')),
                'keywords' => $keywords,
            ],
        ];
    }

    public function preview(Item $item): array
    {
        $analysis = $this->analyzeFromArray($item->toArray());
        $image = $item->seoSocialImageUrl();
        $price = (float) $item->discount_price;

        $analysis['meta'] = [
            'title' => $item->seoTitle(),
            'description' => $item->seoDescription(),
            'keywords' => $item->seoKeywords(),
            'canonical' => $item->seoCanonicalUrl(),
            'robots' => $item->seoRobots(),
            'og_title' => $item->og_title ?: $item->seoTitle(),
            'og_description' => $item->og_description ?: $item->seoDescription(),
            'og_image' => $image,
            'twitter_title' => $item->twitter_title ?: ($item->og_title ?: $item->seoTitle()),
            'twitter_description' => $item->twitter_description ?: ($item->og_description ?: $item->seoDescription()),
            'twitter_image' => $item->twitter_image ? $item->resolveSeoImageUrl($item->twitter_image) : $image,
        ];

        $analysis['schema'] = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $item->name,
            'description' => $item->seoDescription(),
            'sku' => $item->sku,
            'image' => $image,
            'brand' => [
                '@type' => 'Brand',
                'name' => $item->brand->name ?: config('app.name'),
            ],
            'category' => $item->category->name ?: null,
            'offers' => [
                '@type' => 'Offer',
                'url' => $item->seoCanonicalUrl(),
                'priceCurrency' => PriceHelper::setCurrencyName(),
                'price' => number_format($price * PriceHelper::setCurrencyValue(), 2, '.', ''),
                'availability' => $item->is_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
        ];

        $ratingValue = $item->reviews()->where('status', 1)->avg('rating');
        $reviewCount = $item->reviews()->where('status', 1)->count();
        if ($ratingValue && $reviewCount) {
            $analysis['schema']['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $ratingValue, 2),
                'reviewCount' => $reviewCount,
            ];
        }

        return $analysis;
    }

    public function normalizeKeywords(string $value): array
    {
        $value = str_replace(['value', '{', '}', '[', ']', ':', '"'], '', $value);

        return collect(explode(',', $value))
            ->map(fn ($keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function between(int $value, int $min, int $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    private function check(string $label, bool $passed, int $points, string $hint): array
    {
        return compact('label', 'passed', 'points', 'hint');
    }
}
