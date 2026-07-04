<?php

use App\Models\Item;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$updated = 0;
$withMaterial = 0;
$withMeasurements = 0;
$withSizes = 0;
$withColors = 0;
$withoutSpecs = [];
$examples = [];

Item::with(['category', 'attributes.options'])
    ->where('status', 1)
    ->chunkById(100, function ($items) use (
        &$updated,
        &$withMaterial,
        &$withMeasurements,
        &$withSizes,
        &$withColors,
        &$withoutSpecs,
        &$examples
    ) {
        foreach ($items as $item) {
            $text = cleanText((string) $item->details);
            $category = optional($item->category)->name;
            $sizes = optionList($item, 'Tamanho');
            $colors = optionList($item, 'Cor');
            $material = extractMaterial($text);
            $measurements = extractMeasurements($text);

            $names = [];
            $descriptions = [];

            addSpec($names, $descriptions, 'Categoria', $category);
            addSpec($names, $descriptions, 'Material/Tecido', $material);
            addSpec($names, $descriptions, 'Medidas', $measurements);
            addSpec($names, $descriptions, 'Tamanhos disponíveis', $sizes);
            addSpec($names, $descriptions, 'Cores disponíveis', $colors);
            addSpec($names, $descriptions, 'SKU', $item->sku ? '#' . ltrim((string) $item->sku, '#') : null);

            if ($names === []) {
                $withoutSpecs[] = $item->name;
                continue;
            }

            $item->forceFill([
                'is_specification' => 1,
                'specification_name' => json_encode($names, JSON_UNESCAPED_UNICODE),
                'specification_description' => json_encode($descriptions, JSON_UNESCAPED_UNICODE),
            ])->save();

            $updated++;
            $withMaterial += $material !== '' ? 1 : 0;
            $withMeasurements += $measurements !== '' ? 1 : 0;
            $withSizes += $sizes !== '' ? 1 : 0;
            $withColors += $colors !== '' ? 1 : 0;

            if (count($examples) < 8) {
                $examples[] = [
                    'name' => $item->name,
                    'specs' => array_combine($names, $descriptions),
                ];
            }
        }
    });

echo "Produtos atualizados com especificacoes: {$updated}" . PHP_EOL;
echo "Com material/tecido: {$withMaterial}" . PHP_EOL;
echo "Com medidas: {$withMeasurements}" . PHP_EOL;
echo "Com tamanhos: {$withSizes}" . PHP_EOL;
echo "Com cores: {$withColors}" . PHP_EOL;
if ($withoutSpecs !== []) {
    echo "Sem especificacoes geradas: " . implode('; ', array_slice($withoutSpecs, 0, 20)) . PHP_EOL;
}
echo "Exemplos:" . PHP_EOL;
foreach ($examples as $example) {
    echo '- ' . $example['name'] . ': ';
    $parts = [];
    foreach ($example['specs'] as $name => $description) {
        $parts[] = "{$name}={$description}";
    }
    echo implode(' | ', $parts) . PHP_EOL;
}

function addSpec(array &$names, array &$descriptions, string $name, ?string $description): void
{
    $description = trim((string) $description);
    if ($description === '') {
        return;
    }

    $names[] = $name;
    $descriptions[] = $description;
}

function optionList(Item $item, string $attributeName): string
{
    $attribute = $item->attributes->first(function ($attribute) use ($attributeName) {
        return Str::lower((string) $attribute->name) === Str::lower($attributeName);
    });

    if (! $attribute) {
        return '';
    }

    $values = $attribute->options
        ->pluck('name')
        ->map(fn ($name) => trim((string) $name))
        ->filter()
        ->unique()
        ->values()
        ->all();

    return implode(', ', $values);
}

function extractMaterial(string $text): string
{
    $sentences = splitSentences($text);
    $matches = [];

    foreach ($sentences as $sentence) {
        $lower = mb_strtolower($sentence);
        if (
            str_contains($lower, ' é uma peça ')
            || str_starts_with($lower, 'disponível com variações')
            || str_starts_with($lower, 'peça pensada')
            || str_starts_with($lower, 'item pensado')
        ) {
            continue;
        }

        $sentence = preg_replace('/\s*Disponível com variações.+$/iu', '', $sentence);
        $sentence = preg_replace('/\s*Peça pensada.+$/iu', '', (string) $sentence);
        $sentence = preg_replace('/\s*Item pensado.+$/iu', '', (string) $sentence);
        $sentence = trim((string) $sentence);

        if (
            str_contains($lower, 'tecido')
            || str_contains($lower, 'algod')
            || str_contains($lower, 'poli')
            || str_contains($lower, 'elastano')
            || str_contains($lower, 'viscose')
            || str_contains($lower, 'linho')
            || str_contains($lower, 'jeans')
            || str_contains($lower, 'moletom')
            || str_contains($lower, 'moletim')
            || str_contains($lower, 'felpa')
            || str_contains($lower, 'tricot')
            || str_contains($lower, 'tricô')
            || preg_match('/\d+\s*%/u', $sentence)
        ) {
            $matches[] = trim($sentence, " \t\n\r\0\x0B.;");
        }
    }

    $matches = array_values(array_unique(array_filter($matches)));
    return implode('; ', array_slice($matches, 0, 3));
}

function extractMeasurements(string $text): string
{
    $sentences = splitSentences($text);
    $matches = [];

    foreach ($sentences as $sentence) {
        if (preg_match('/\d+(?:[,.]\d+)?\s*(?:cm|m)\b/iu', $sentence)) {
            $matches[] = trim($sentence, " \t\n\r\0\x0B.;");
        }
    }

    $matches = array_values(array_unique(array_filter($matches)));
    return implode('; ', array_slice($matches, 0, 2));
}

function splitSentences(string $text): array
{
    $text = cleanText($text);
    $parts = preg_split('/(?<=[.!?])\s+|\n+/u', $text) ?: [];

    return array_values(array_filter(array_map('trim', $parts)));
}

function cleanText(string $text): string
{
    $text = preg_replace('/<\s*br\s*\/?>/iu', "\n", $text);
    $text = preg_replace('/<\s*\/\s*(p|div|li|tr|h[1-6])\s*>/iu', "\n", (string) $text);
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\xc2\xa0", "\r"], ' ', $text);
    $text = preg_replace('/[ \t]+/u', ' ', $text);
    $text = preg_replace('/\n\s+/u', "\n", (string) $text);
    $text = preg_replace('/\n{2,}/u', "\n", (string) $text);
    $text = preg_replace('/\s+([,.;:])/u', '$1', $text);

    return trim((string) $text);
}
