<?php

use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cacheDir = storage_path('app/rataplam_wix_product_pages');

if (! is_dir($cacheDir)) {
    fwrite(STDERR, "Cache do Wix nao encontrado: {$cacheDir}\n");
    exit(1);
}

$files = glob($cacheDir . '/*.html') ?: [];
$graphqlToken = null;
$syncedItems = 0;
$attributesCreated = 0;
$optionsCreated = 0;
$withoutOptions = [];
$notFound = [];
$examples = [];
$attributeTotals = [];

DB::transaction(function () use (
    $files,
    $graphqlToken,
    &$syncedItems,
    &$attributesCreated,
    &$optionsCreated,
    &$withoutOptions,
    &$notFound,
    &$examples,
    &$attributeTotals
) {
    AttributeOption::query()->delete();
    Attribute::query()->delete();

    foreach ($files as $file) {
        $product = extractProductFromProductPage((string) file_get_contents($file));
        $slugFromFile = basename($file, '.html');
        if (! $product || normalizeProductOptions($product) === []) {
            $graphqlProduct = fetchProductFromGraphql($slugFromFile, $graphqlToken);
            if ($graphqlProduct) {
                $product = $graphqlProduct;
            }
        }

        if (! $product) {
            $withoutOptions[] = basename($file) . ' (produto nao encontrado no cache)';
            continue;
        }

        $name = cleanValue((string) ($product['name'] ?? ''));
        $urlPart = cleanValue((string) ($product['urlPart'] ?? ''));
        $item = findItem($name, $urlPart);

        if (! $item) {
            $notFound[] = $name ?: basename($file);
            continue;
        }

        $options = normalizeProductOptions($product);
        if ($options === []) {
            $withoutOptions[] = $item->name;
            $syncedItems++;
            continue;
        }

        foreach ($options as $attributeName => $optionNames) {
            $attribute = Attribute::create([
                'item_id' => $item->id,
                'name' => $attributeName,
                'keyword' => Str::slug($attributeName),
            ]);
            $attributesCreated++;
            $attributeTotals[$attributeName] = ($attributeTotals[$attributeName] ?? 0) + 1;

            foreach ($optionNames as $optionName) {
                AttributeOption::create([
                    'attribute_id' => $attribute->id,
                    'name' => $optionName,
                    'keyword' => Str::slug($optionName),
                    'price' => 0,
                    'stock' => 'unlimited',
                ]);
                $optionsCreated++;
            }
        }

        if (count($examples) < 8) {
            $examples[] = [
                'name' => $item->name,
                'options' => $options,
            ];
        }

        $syncedItems++;
    }
});

$itemsWithAttributes = Item::whereHas('attributes.options')->count();
$itemsWithoutAttributes = Item::whereDoesntHave('attributes.options')->count();

echo "Paginas no cache: " . count($files) . PHP_EOL;
echo "Produtos sincronizados: {$syncedItems}" . PHP_EOL;
echo "Atributos criados: {$attributesCreated}" . PHP_EOL;
echo "Opcoes criadas: {$optionsCreated}" . PHP_EOL;
echo "Produtos com atributos/opcoes: {$itemsWithAttributes}" . PHP_EOL;
echo "Produtos sem atributos/opcoes na fonte: {$itemsWithoutAttributes}" . PHP_EOL;
echo "Atributos por tipo:" . PHP_EOL;
foreach ($attributeTotals as $name => $count) {
    echo "- {$name}: {$count}" . PHP_EOL;
}
if ($notFound !== []) {
    echo "Nao encontrados no banco: " . implode('; ', array_slice($notFound, 0, 20)) . PHP_EOL;
}
if ($withoutOptions !== []) {
    echo "Sem opcoes no Wix: " . count($withoutOptions) . PHP_EOL;
    echo implode('; ', array_slice($withoutOptions, 0, 20)) . PHP_EOL;
}
echo "Exemplos:" . PHP_EOL;
foreach ($examples as $example) {
    echo '- ' . $example['name'] . ': ';
    $parts = [];
    foreach ($example['options'] as $attribute => $values) {
        $parts[] = $attribute . '=' . implode(', ', $values);
    }
    echo implode(' | ', $parts) . PHP_EOL;
}

function extractProductFromProductPage(string $html): ?array
{
    if (! preg_match('/<script[^>]+id=["\']wix-warmup-data["\'][^>]*>(.*?)<\/script>/is', $html, $match)) {
        return null;
    }

    $decoded = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
    if (! is_array($decoded)) {
        return null;
    }

    return findProductNode($decoded);
}

function findProductNode(array $node): ?array
{
    if (isset($node['catalog']['product']) && is_array($node['catalog']['product'])) {
        return $node['catalog']['product'];
    }

    foreach ($node as $value) {
        if (is_array($value)) {
            $found = findProductNode($value);
            if ($found !== null) {
                return $found;
            }
        }
    }

    return null;
}

function normalizeProductOptions(array $product): array
{
    $result = [];

    foreach (($product['options'] ?? []) as $option) {
        $attributeName = normalizeAttributeName((string) ($option['title'] ?? $option['key'] ?? ''));
        if ($attributeName === '') {
            continue;
        }

        $values = [];
        foreach (($option['selections'] ?? []) as $selection) {
            $value = cleanValue((string) ($selection['description'] ?? $selection['value'] ?? $selection['key'] ?? ''));
            if ($value !== '') {
                $values[] = normalizeOptionValue($attributeName, $value);
            }
        }

        $values = array_values(array_unique(array_filter($values)));
        if ($values !== []) {
            $result[$attributeName] = sortOptionValues($attributeName, $values);
        }
    }

    return $result;
}

function normalizeAttributeName(string $name): string
{
    $clean = cleanValue($name);
    $lower = mb_strtolower($clean);

    return match (true) {
        in_array($lower, ['tamanho', 'size', 'tam'], true) => 'Tamanho',
        default => 'Cor',
    };
}

function normalizeOptionValue(string $attributeName, string $value): string
{
    $value = cleanValue($value);

    if ($attributeName === 'Tamanho') {
        $upper = mb_strtoupper($value);
        $upper = str_replace(['TAM ', 'TAM. ', 'TAMANHO '], '', $upper);
        if (preg_match('/^\d+$/', $upper)) {
            return str_pad($upper, 2, '0', STR_PAD_LEFT);
        }

        return $upper;
    }

    if ($attributeName === 'Cor') {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    return $value;
}

function sortOptionValues(string $attributeName, array $values): array
{
    if ($attributeName !== 'Tamanho') {
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);
        return $values;
    }

    $order = ['RN', 'PP', 'P', 'M', 'G', 'GG', 'EG', 'U'];
    usort($values, function (string $left, string $right) use ($order) {
        $leftNumeric = ctype_digit($left);
        $rightNumeric = ctype_digit($right);

        if ($leftNumeric && $rightNumeric) {
            return (int) $left <=> (int) $right;
        }

        if ($leftNumeric !== $rightNumeric) {
            return $leftNumeric ? -1 : 1;
        }

        $leftIndex = array_search($left, $order, true);
        $rightIndex = array_search($right, $order, true);
        if ($leftIndex !== false || $rightIndex !== false) {
            return ($leftIndex === false ? 999 : $leftIndex) <=> ($rightIndex === false ? 999 : $rightIndex);
        }

        return strnatcasecmp($left, $right);
    });

    return $values;
}

function findItem(string $name, string $urlPart): ?Item
{
    $slug = Str::slug($urlPart ?: $name);

    return Item::where('slug', $slug)->first()
        ?: Item::where('name', $name)->first();
}

function cleanValue(string $value): string
{
    $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = preg_replace('/\s+/u', ' ', $value);

    return trim((string) $value);
}

function fetchProductFromGraphql(string $slug, ?string &$graphqlToken): ?array
{
    $graphqlToken ??= fetchWixStoresToken();
    if (! $graphqlToken) {
        return null;
    }

    $query = <<<'GRAPHQL'
query getProductBySlug($slug: String!) {
  catalog {
    product(slug: $slug, onlyVisible: true) {
      id
      name
      urlPart
      options {
        title
        key
        selections {
          value
          description
          key
        }
      }
    }
  }
}
GRAPHQL;

    $payload = json_encode([
        'query' => $query,
        'variables' => ['slug' => $slug],
        'operationName' => 'getProductBySlug',
    ], JSON_UNESCAPED_UNICODE);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: application/json',
                'Content-Type: application/json',
                'Referer: https://www.rataplam.com.br/pagina-de-produto/' . rawurlencode($slug),
                'Authorization: ' . $graphqlToken,
                'X-Wix-Client-Artifact-Id: wixstores-client-product-page',
            ]),
            'content' => $payload,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ]);

    $response = file_get_contents('https://www.rataplam.com.br/_api/wixstores-graphql-server/graphql', false, $context);
    if ($response === false) {
        return null;
    }

    $decoded = json_decode($response, true);

    return $decoded['data']['catalog']['product'] ?? null;
}

function fetchWixStoresToken(): ?string
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: application/json',
                'Referer: https://www.rataplam.com.br/',
            ]),
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ]);

    $response = file_get_contents('https://www.rataplam.com.br/_api/v1/access-tokens', false, $context);
    if ($response === false) {
        return null;
    }

    $decoded = json_decode($response, true);

    return $decoded['apps']['1380b703-ce81-ff05-f115-39571d94dfcd']['accessToken'] ?? null;
}
