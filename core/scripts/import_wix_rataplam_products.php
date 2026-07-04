<?php

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sourceUrl = 'https://www.rataplam.com.br/loja?page=99';
$categoryName = 'Produtos Rataplam';
$categorySlug = Str::slug($categoryName);
$context = stream_context_create([
    'http' => [
        'timeout' => 60,
        'header' => "User-Agent: RataplamImporter/1.0\r\n",
    ],
]);

$html = file_get_contents($sourceUrl, false, $context);
if ($html === false) {
    fwrite(STDERR, "Nao foi possivel acessar {$sourceUrl}\n");
    exit(1);
}

$products = extractWixProducts($html);
if (! $products) {
    fwrite(STDERR, "Nenhum produto encontrado no warmup do Wix.\n");
    exit(1);
}

$category = Category::firstOrCreate(
    ['slug' => $categorySlug],
    [
        'name' => $categoryName,
        'status' => 1,
        'is_feature' => 1,
        'serial' => 0,
        'meta_keywords' => 'rataplam,produto infantil,moda infantil',
        'meta_descriptions' => 'Moda infantil Rataplam com peças confortáveis para a rotina, passeio e lazer.',
    ]
);

$stats = [
    'total_origem' => count($products),
    'disponiveis' => 0,
    'criados' => 0,
    'atualizados' => 0,
    'ignorados_sem_estoque' => 0,
    'fotos_baixadas' => 0,
    'fotos_reutilizadas' => 0,
    'falhas_foto' => 0,
];

DB::beginTransaction();

try {
    foreach ($products as $product) {
        if (empty($product['isInStock'])) {
            $stats['ignorados_sem_estoque']++;
            continue;
        }

        $stats['disponiveis']++;

        $name = trim((string) ($product['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $oldId = (string) ($product['id'] ?? '');
        $slug = uniqueStableSlug((string) ($product['urlPart'] ?? ''), $name);
        $imageNames = importProductImages($product, $stats);
        $price = decimalValue($product['price'] ?? 0);
        $comparePrice = decimalValue($product['comparePrice'] ?? 0);
        $stock = stockQuantity($product);
        $sku = trim((string) ($product['sku'] ?? '')) ?: 'RAT-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $oldId), 0, 10));
        $details = productDetails($product, $sourceUrl);

        $item = Item::where('slug', $slug)->first();
        $created = ! $item;

        $item = Item::updateOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $category->id,
                'subcategory_id' => 0,
                'childcategory_id' => 0,
                'brand_id' => 0,
                'tax_id' => null,
                'name' => $name,
                'sku' => $sku,
                'tags' => 'rataplam,moda infantil,produto infantil',
                'sort_details' => Str::limit(strip_tags($details), 180, ''),
                'details' => $details,
                'photo' => $imageNames[0] ?? 'placeholder.png',
                'thumbnail' => $imageNames[0] ?? 'placeholder.png',
                'discount_price' => $price,
                'previous_price' => $comparePrice > $price ? $comparePrice : 0,
                'stock' => $stock,
                'meta_keywords' => 'rataplam,moda infantil,' . $name,
                'meta_description' => Str::limit(strip_tags($details), 250, ''),
                'status' => 1,
                'is_type' => 'new',
                'item_type' => 'normal',
                'video' => null,
                'date' => null,
                'file' => null,
                'link' => null,
                'file_type' => null,
                'license_name' => null,
                'license_key' => null,
                'affiliate_link' => null,
            ]
        );

        syncGallery($item, $imageNames);

        if ($created) {
            $stats['criados']++;
        } else {
            $stats['atualizados']++;
        }
    }

    DB::commit();
} catch (Throwable $exception) {
    DB::rollBack();
    throw $exception;
}

foreach ($stats as $key => $value) {
    echo $key . '=' . $value . PHP_EOL;
}

function extractWixProducts(string $html): array
{
    preg_match_all('/<script[^>]*type="application\/json"[^>]*>(.*?)<\/script>/is', $html, $matches);

    foreach ($matches[1] as $json) {
        $decoded = json_decode(html_entity_decode($json, ENT_QUOTES | ENT_HTML5), true);
        if (! is_array($decoded)) {
            continue;
        }

        $warmup = $decoded['appsWarmupData']['1380b703-ce81-ff05-f115-39571d94dfcd'] ?? null;
        if (! is_array($warmup)) {
            continue;
        }

        foreach ($warmup as $entry) {
            $products = $entry['catalog']['category']['productsWithMetaData']['list'] ?? null;
            if (is_array($products)) {
                return $products;
            }
        }
    }

    return [];
}

function uniqueStableSlug(string $urlPart, string $name): string
{
    $base = Str::slug($urlPart ?: $name);
    return $base ?: Str::slug($name);
}

function importProductImages(array $product, array &$stats): array
{
    $names = [];
    $media = $product['media'] ?? [];

    foreach ($media as $index => $image) {
        $url = $image['fullUrl'] ?? null;
        if (! $url) {
            continue;
        }

        $oldId = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($product['id'] ?? 'produto'));
        $filename = 'rataplam_' . substr($oldId, 0, 12) . '_' . ($index + 1) . '.jpg';
        $storagePath = storage_path('app/public/images/' . $filename);
        $publicPath = public_path('storage/images/' . $filename);

        if (is_file($storagePath) && is_file($publicPath)) {
            $names[] = $filename;
            $stats['fotos_reutilizadas']++;
            continue;
        }

        $body = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'timeout' => 45,
                'header' => "User-Agent: RataplamImporter/1.0\r\n",
            ],
        ]));

        if ($body === false || strlen($body) < 100) {
            $stats['falhas_foto']++;
            continue;
        }

        ensureDir(dirname($storagePath));
        ensureDir(dirname($publicPath));
        file_put_contents($storagePath, $body);
        file_put_contents($publicPath, $body);
        $names[] = $filename;
        $stats['fotos_baixadas']++;
    }

    return $names;
}

function ensureDir(string $dir): void
{
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function decimalValue(mixed $value): float
{
    return round((float) $value, 2);
}

function stockQuantity(array $product): int
{
    $quantity = (int) ($product['inventory']['quantity'] ?? 0);
    if (! empty($product['isTrackingInventory']) && $quantity > 0) {
        return $quantity;
    }

    return ! empty($product['isInStock']) ? 100 : 0;
}

function productDetails(array $product, string $sourceUrl): string
{
    $lines = [
        '<p>Peça infantil Rataplam pensada para oferecer conforto, praticidade e um visual delicado no dia a dia.</p>',
        '<ul>',
        '<li>Preço: ' . e((string) ($product['formattedPrice'] ?? '')) . '</li>',
    ];

    if (! empty($product['formattedComparePrice'])) {
        $lines[] = '<li>Preço anterior: ' . e((string) $product['formattedComparePrice']) . '</li>';
    }

    $lines[] = '</ul>';

    return implode('', $lines);
}

function syncGallery(Item $item, array $imageNames): void
{
    $galleryImages = array_values(array_slice($imageNames, 1));

    if (! $galleryImages) {
        return;
    }

    foreach ($galleryImages as $imageName) {
        Gallery::firstOrCreate([
            'item_id' => $item->id,
            'photo' => $imageName,
        ]);
    }
}
