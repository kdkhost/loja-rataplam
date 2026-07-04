<?php

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sourceUrl = 'https://www.rataplam.com.br/loja?page=99';
$productPageBase = 'https://www.rataplam.com.br/pagina-de-produto/';
$cacheDir = storage_path('app/rataplam_wix_product_pages');

if (! is_dir($cacheDir)) {
    mkdir($cacheDir, 0775, true);
}

$products = extractProducts(fetchUrl($sourceUrl));
$available = array_values(array_filter($products, fn (array $product) => ($product['isInStock'] ?? false) === true));

$updated = 0;
$withoutLegacyDescription = 0;
$failed = [];
$examples = [];

foreach ($available as $index => $product) {
    $name = trim((string) ($product['name'] ?? ''));
    $urlPart = trim((string) ($product['urlPart'] ?? ''));
    $slug = Str::slug($urlPart ?: $name);

    if ($name === '' || $urlPart === '') {
        $failed[] = $name ?: '(sem nome)';
        continue;
    }

    $item = Item::where('slug', $slug)->first();
    if (! $item) {
        $item = Item::where('name', $name)->first();
    }

    if (! $item) {
        $failed[] = $name . ' (nao encontrado no banco)';
        continue;
    }

    $legacyDescription = fetchLegacyDescription($productPageBase, $urlPart, $cacheDir);
    if ($legacyDescription === '') {
        $withoutLegacyDescription++;
        $legacyDescription = inferFallbackDescription($name, $product);
    }

    $categoryName = optional($item->category)->name ?: inferCategoryName($name);
    $details = buildCompletedDescription($name, $legacyDescription, $categoryName, $product);
    $short = Str::limit(strip_tags($details), 190, '');

    $item->update([
        'details' => $details,
        'sort_details' => $short,
        'meta_description' => Str::limit(strip_tags($details), 250, ''),
        'meta_keywords' => buildKeywords($name, $categoryName, $legacyDescription),
        'tags' => buildTags($name, $categoryName, $legacyDescription),
    ]);

    $updated++;
    if (count($examples) < 6) {
        $examples[] = [
            'name' => $name,
            'legacy' => $legacyDescription,
            'category' => $categoryName,
        ];
    }

    if (($index + 1) % 20 === 0) {
        echo "Processados " . ($index + 1) . '/' . count($available) . PHP_EOL;
    }

    usleep(180000);
}

$forbidden = Item::where('name', 'like', '%')
    ->where(function ($query) {
        foreach (['importado', 'site antigo', 'origem', 'catalogo antigo', 'catálogo antigo'] as $term) {
            $query->orWhere('details', 'like', '%' . $term . '%');
        }
    })
    ->count();

echo PHP_EOL;
echo "Produtos disponiveis na fonte: " . count($available) . PHP_EOL;
echo "Produtos atualizados: {$updated}" . PHP_EOL;
echo "Sem descricao textual na pagina antiga: {$withoutLegacyDescription}" . PHP_EOL;
echo "Termos proibidos encontrados nas descricoes: {$forbidden}" . PHP_EOL;
if ($failed !== []) {
    echo "Falhas: " . implode('; ', array_slice($failed, 0, 20)) . PHP_EOL;
}
echo "Exemplos:" . PHP_EOL;
foreach ($examples as $example) {
    echo '- ' . $example['name'] . ' [' . $example['category'] . ']: ' . $example['legacy'] . PHP_EOL;
}

function fetchLegacyDescription(string $baseUrl, string $urlPart, string $cacheDir): string
{
    $cacheFile = $cacheDir . '/' . Str::slug($urlPart) . '.html';
    if (is_file($cacheFile) && filesize($cacheFile) > 1000) {
        $html = file_get_contents($cacheFile);
    } else {
        $html = fetchUrl($baseUrl . rawurlencode($urlPart));
        file_put_contents($cacheFile, $html);
    }

    $description = extractJsonLdDescription($html);
    if ($description === '') {
        $description = extractMetaContent($html, 'og:description') ?: extractMetaContent($html, 'twitter:description');
    }

    return cleanLegacyText($description);
}

function extractJsonLdDescription(string $html): string
{
    if (! preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
        return '';
    }

    foreach ($matches[1] as $json) {
        $decoded = json_decode(html_entity_decode($json, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
        if (! is_array($decoded)) {
            continue;
        }

        $items = array_is_list($decoded) ? $decoded : [$decoded];
        foreach ($items as $item) {
            if (($item['@type'] ?? null) === 'Product' && ! empty($item['description'])) {
                return (string) $item['description'];
            }
        }
    }

    return '';
}

function extractMetaContent(string $html, string $property): string
{
    $quoted = preg_quote($property, '/');
    if (preg_match('/<meta[^>]+(?:property|name)=["\']' . $quoted . '["\'][^>]+content=["\']([^"\']*)["\']/i', $html, $match)) {
        return html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return '';
}

function buildCompletedDescription(string $name, string $legacyDescription, string $categoryName, array $product): string
{
    $legacyDescription = cleanLegacyText($legacyDescription);
    $categoryIntro = categoryIntro($name, $categoryName);
    $options = optionNames($product);

    $paragraphs = [
        '<p><strong>' . e($name) . '</strong> ' . e($categoryIntro) . '</p>',
        '<p>' . e($legacyDescription) . '</p>',
    ];

    if ($options !== '') {
        $paragraphs[] = '<p>Disponível com variações de ' . e(mb_strtolower($options)) . ', conforme seleção no produto.</p>';
    }

    $paragraphs[] = '<p>' . e(finalUseParagraph($name, $categoryName)) . '</p>';

    return implode("\n", $paragraphs);
}

function categoryIntro(string $name, string $categoryName): string
{
    $lower = mb_strtolower($categoryName);
    $nameLower = mb_strtolower($name);

    return match (true) {
        str_contains($nameLower, 'toalha') => 'é um item infantil para banho, enxoval e cuidados do dia a dia.',
        str_contains($lower, 'banho'), str_contains($nameLower, 'uv') => 'é uma peça de moda praia infantil para piscina, praia e lazer ao ar livre.',
        str_contains($lower, 'pijama') => 'é uma peça infantil para descanso, sono e momentos de conforto em casa.',
        str_contains($lower, 'casaco') => 'é uma peça infantil para sobreposição, ideal para dias mais frescos.',
        str_contains($lower, 'vestido') => 'é uma peça infantil com caimento delicado para passeios e uso casual.',
        str_contains($lower, 'conjunto') => 'é um conjunto infantil coordenado para facilitar a composição do look.',
        str_contains($lower, 'bermuda'), str_contains($lower, 'short') => 'é uma peça infantil prática para movimento, brincadeiras e rotina.',
        str_contains($lower, 'calça') => 'é uma peça infantil versátil para compor looks confortáveis.',
        default => 'é uma peça de moda infantil selecionada para a rotina, passeio e lazer.',
    };
}

function finalUseParagraph(string $name, string $categoryName): string
{
    $nameLower = mb_strtolower($name);
    $categoryLower = mb_strtolower($categoryName);

    if (str_contains($nameLower, 'toalha') || str_contains($categoryLower, 'enxoval')) {
        return 'Item pensado para uso infantil com toque confortável, bom acabamento e praticidade na rotina.';
    }

    return 'Peça pensada para vestir com conforto, bom acabamento e visual adequado ao dia a dia infantil.';
}

function inferFallbackDescription(string $name, array $product): string
{
    $parts = [$name];
    $options = optionNames($product);
    if ($options !== '') {
        $parts[] = 'com variações de ' . mb_strtolower($options);
    }

    return implode(' ', $parts) . '.';
}

function optionNames(array $product): string
{
    $options = [];
    foreach (($product['options'] ?? []) as $option) {
        if (! empty($option['key'])) {
            $options[] = trim((string) $option['key']);
        }
    }

    return implode(' e ', array_unique($options));
}

function buildKeywords(string $name, string $categoryName, string $legacyDescription): string
{
    $words = array_filter([
        $name,
        $categoryName,
        'moda infantil',
        'rataplam',
        compositionKeyword($legacyDescription),
    ]);

    return implode(', ', array_unique($words));
}

function buildTags(string $name, string $categoryName, string $legacyDescription): string
{
    return buildKeywords($name, $categoryName, $legacyDescription);
}

function compositionKeyword(string $legacyDescription): ?string
{
    if (preg_match('/algod[aã]o|poli[eé]ster|elastano|viscose|linho|jeans|moletim|tric[oô]|uv/i', $legacyDescription, $match)) {
        return mb_strtolower($match[0]);
    }

    return null;
}

function cleanLegacyText(string $text): string
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = str_replace(
        ['Pol!este', 'Pol!éster', 'Poliester', 'Algodao', 'algodâo', 'elastico', 'Elastico', 'aproximda', 'meiaa malha', 'meiaa'],
        ['Poliéster', 'Poliéster', 'Poliéster', 'Algodão', 'algodão', 'elástico', 'Elástico', 'aproximada', 'meia malha', 'meia'],
        $text
    );
    $text = preg_replace('/(algod[aã]o)(medida)/iu', '$1, $2', $text);
    $text = preg_replace('/(\d+\s*cm)(cor)/iu', '$1, $2', $text);
    $text = preg_replace('/(branc[ao])(\s*com)/iu', '$1$2', $text);
    $text = preg_replace('/\s+([,.;:])/u', '$1', $text);
    $text = preg_replace('/([.;:])(?=\S)/u', '$1 ', $text);
    $text = preg_replace('/\s{2,}/u', ' ', $text);

    return trim($text);
}

function inferCategoryName(string $name): string
{
    $lower = mb_strtolower($name);

    return match (true) {
        str_contains($lower, 'vestido') => 'Vestidos',
        str_contains($lower, 'pijama') => 'Pijamas',
        str_contains($lower, 'casaco'), str_contains($lower, 'colete') => 'Casacos',
        str_contains($lower, 'conjunto') => 'Conjuntos',
        str_contains($lower, 'bermuda') => 'Bermudas',
        str_contains($lower, 'short'), str_contains($lower, 'saia') => 'Shorts e Saias',
        str_contains($lower, 'calça') => 'Calças',
        str_contains($lower, 'sunga') => 'Sungas',
        str_contains($lower, 'maiô'), str_contains($lower, 'maio') => 'Maiôs',
        str_contains($lower, 'biquini'), str_contains($lower, 'biquíni') => 'Biquínis',
        str_contains($lower, 'toalha') => 'Roupas de Banho',
        default => 'Moda Infantil',
    };
}

function extractProducts(string $html): array
{
    preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $matches);

    foreach ($matches[1] as $script) {
        if (! str_contains($script, 'productsWithMetaData')) {
            continue;
        }

        $decoded = json_decode(trim($script), true);
        if (! is_array($decoded)) {
            continue;
        }

        $products = findProductsList($decoded);
        if ($products !== null) {
            return $products;
        }
    }

    throw new RuntimeException('Lista de produtos nao encontrada no HTML antigo.');
}

function findProductsList(array $node): ?array
{
    if (isset($node['productsWithMetaData']['list']) && is_array($node['productsWithMetaData']['list'])) {
        return $node['productsWithMetaData']['list'];
    }

    foreach ($node as $value) {
        if (is_array($value)) {
            $found = findProductsList($value);
            if ($found !== null) {
                return $found;
            }
        }
    }

    return null;
}

function fetchUrl(string $url): string
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: pt-BR,pt;q=0.9,en;q=0.8',
            ]),
            'timeout' => 35,
            'ignore_errors' => true,
        ],
    ]);

    $html = file_get_contents($url, false, $context);
    $status = $http_response_header[0] ?? '';
    if ($html === false || str_contains($status, '429')) {
        throw new RuntimeException('Falha ao baixar URL: ' . $url . ' status=' . $status);
    }

    return $html;
}
