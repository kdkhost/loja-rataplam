<?php

use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$categoryNames = [
    'Bermudas',
    'Biquínis',
    'Blusas',
    'Bodies',
    'Calças',
    'Casacos',
    'Conjuntos',
    'Macacões',
    'Maiôs',
    'Pijamas',
    'Roupas de Banho',
    'Shorts e Saias',
    'Sungas',
    'Vestidos',
    'Acessórios',
    'Enxoval',
    'Moda Infantil',
];

$categories = [];
foreach ($categoryNames as $index => $name) {
    $categories[$name] = Category::updateOrCreate(
        ['slug' => Str::slug($name)],
        [
            'name' => $name,
            'status' => 1,
            'is_feature' => 1,
            'serial' => $index + 1,
            'meta_keywords' => Str::slug($name, ','),
            'meta_descriptions' => 'Categoria de ' . mb_strtolower($name) . ' da loja Rataplam.',
        ]
    );
}

$stats = [
    'produtos_processados' => 0,
    'descricoes_atualizadas' => 0,
    'categorias_atualizadas' => 0,
];

Item::where('tags', 'like', '%rataplam%')
    ->orderBy('id')
    ->chunkById(100, function ($items) use ($categories, &$stats) {
        foreach ($items as $item) {
            $targetCategoryName = classifyProduct($item->name);
            $targetCategoryId = $categories[$targetCategoryName]->id;
            $description = buildProductDescription($item->name, $targetCategoryName);

            $updates = [
                'category_id' => $targetCategoryId,
                'details' => $description,
                'sort_details' => buildShortDescription($item->name, $targetCategoryName),
                'meta_description' => Str::limit(strip_tags($description), 250, ''),
                'meta_keywords' => buildKeywords($item->name, $targetCategoryName),
            ];

            if ((int) $item->category_id !== (int) $targetCategoryId) {
                $stats['categorias_atualizadas']++;
            }

            if (trim((string) $item->details) !== $description) {
                $stats['descricoes_atualizadas']++;
            }

            $item->update($updates);
            $stats['produtos_processados']++;
        }
    });

foreach ($stats as $key => $value) {
    echo $key . '=' . $value . PHP_EOL;
}

echo "categorias\n";
foreach ($categories as $name => $category) {
    echo $name . '=' . Item::where('tags', 'like', '%rataplam%')->where('category_id', $category->id)->count() . PHP_EOL;
}

function classifyProduct(string $name): string
{
    $normalized = normalizeText($name);

    $rules = [
        'Biquínis' => ['biquini', 'biquini'],
        'Maiôs' => ['maio', 'maiô'],
        'Sungas' => ['sunga'],
        'Roupas de Banho' => ['uv ', ' uv', 'praia', 'banho'],
        'Bermudas' => ['bermuda'],
        'Shorts e Saias' => ['short', 'saia', 'shorts'],
        'Blusas' => ['blusa', 'camiseta', 'regata', 'cropped', 'top'],
        'Bodies' => ['body'],
        'Calças' => ['calca', 'calça', 'legging', 'pantacour'],
        'Casacos' => ['casaco', 'jaqueta', 'moletom'],
        'Conjuntos' => ['conjunto', 'cj ', 'kit'],
        'Macacões' => ['macacao', 'macacão', 'jardineira'],
        'Pijamas' => ['pijama'],
        'Vestidos' => ['vestido'],
        'Acessórios' => ['acessorio', 'acessório', 'bolsa', 'bone', 'boné', 'chapeu', 'chapéu', 'meia', 'touca'],
        'Enxoval' => ['enxoval', 'manta', 'fralda', 'toalha', 'cueiro'],
    ];

    foreach ($rules as $category => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($normalized, normalizeText($needle))) {
                return $category;
            }
        }
    }

    return 'Moda Infantil';
}

function buildProductDescription(string $name, string $category): string
{
    $fabric = fabricDescription($name, $category);
    $use = useDescription($category);
    $finish = finishDescription($name, $category);

    return '<p><strong>' . e($name) . '</strong> é uma peça infantil pensada para unir conforto, praticidade e visual delicado no dia a dia.</p>'
        . '<p>' . e($fabric) . ' ' . e($finish) . '</p>'
        . '<p>' . e($use) . '</p>'
        . '<ul>'
        . '<li>Modelagem infantil confortável para brincar, passear e acompanhar a rotina.</li>'
        . '<li>Peça fácil de combinar com outros itens do guarda-roupa.</li>'
        . '<li>Acabamento pensado para vestir bem sem abrir mão da mobilidade.</li>'
        . '<li>Recomendação: conferir a etiqueta da peça para composição exata do tecido e cuidados de lavagem.</li>'
        . '</ul>';
}

function buildShortDescription(string $name, string $category): string
{
    return match ($category) {
        'Biquínis', 'Maiôs', 'Sungas', 'Roupas de Banho' => $name . ' para praia e piscina, com caimento confortável e visual infantil alegre.',
        'Calças' => $name . ' infantil com caimento confortável para uso diário e combinações versáteis.',
        'Bermudas', 'Shorts e Saias' => $name . ' infantil leve e prática para dias de brincadeira, passeio e rotina.',
        'Blusas', 'Bodies' => $name . ' infantil em proposta confortável, fácil de combinar e ideal para o dia a dia.',
        'Casacos' => $name . ' infantil para compor looks confortáveis em dias mais frescos.',
        'Vestidos' => $name . ' infantil com visual delicado e caimento confortável.',
        default => $name . ' infantil com acabamento confortável e visual versátil.',
    };
}

function fabricDescription(string $name, string $category): string
{
    $normalized = normalizeText($name);

    if (str_contains($normalized, 'jeans')) {
        return 'Confeccionada em tecido jeans/denim, oferece estrutura, resistência e um toque casual para composições infantis.';
    }

    if (str_contains($normalized, 'moletim') || str_contains($normalized, 'moletom')) {
        return 'Confeccionada em malha de moletom, entrega toque macio, conforto térmico leve e boa liberdade de movimento.';
    }

    if (str_contains($normalized, 'legging') || str_contains($normalized, 'montaria')) {
        return 'Confeccionada em malha com elasticidade, acompanha os movimentos e mantém o caimento ajustado ao corpo.';
    }

    if (str_contains($normalized, 'uv')) {
        return 'Confeccionada em tecido próprio para moda praia com proposta de proteção UV, ideal para uso ao sol, praia e piscina.';
    }

    return match ($category) {
        'Biquínis', 'Maiôs', 'Sungas', 'Roupas de Banho' => 'Confeccionada em tecido de moda praia com elasticidade, toque leve e secagem rápida.',
        'Blusas', 'Bodies' => 'Confeccionada em malha confortável, com toque macio e boa respirabilidade para a rotina infantil.',
        'Calças', 'Bermudas', 'Shorts e Saias' => 'Confeccionada em tecido confortável e resistente para acompanhar a movimentação das crianças.',
        'Casacos' => 'Confeccionada em tecido mais encorpado, ideal para compor looks em dias amenos ou mais frescos.',
        'Vestidos', 'Macacões', 'Conjuntos' => 'Confeccionada em tecido confortável, com caimento leve e toque agradável para o uso infantil.',
        default => 'Confeccionada com material confortável e acabamento adequado para o uso infantil.',
    };
}

function finishDescription(string $name, string $category): string
{
    $normalized = normalizeText($name);

    if (str_contains($normalized, 'tubarao')) {
        return 'A estampa com tema de tubarão traz um toque divertido e combina bem com produções de verão.';
    }

    if (str_contains($normalized, 'dino')) {
        return 'A proposta com tema de dinossauro deixa a peça divertida e fácil de usar em looks casuais.';
    }

    if (str_contains($normalized, 'flor') || str_contains($normalized, 'primavera') || str_contains($normalized, 'jardim')) {
        return 'A proposta floral deixa o visual delicado, alegre e fácil de coordenar.';
    }

    if (str_contains($normalized, 'basic')) {
        return 'O visual básico facilita combinações e torna a peça útil em várias ocasiões.';
    }

    return match ($category) {
        'Biquínis', 'Maiôs', 'Sungas', 'Roupas de Banho' => 'O caimento foi pensado para conforto durante brincadeiras na água e momentos de lazer.',
        'Bermudas', 'Shorts e Saias', 'Calças' => 'A modelagem favorece mobilidade, tornando a peça prática para escola, passeio e brincadeiras.',
        'Blusas', 'Bodies' => 'O acabamento valoriza conforto no vestir e facilita o uso com calças, shorts, saias e bermudas.',
        'Vestidos' => 'O caimento valoriza um visual delicado sem perder a praticidade para a criança.',
        default => 'O acabamento segue uma proposta versátil para facilitar combinações no guarda-roupa infantil.',
    };
}

function useDescription(string $category): string
{
    return match ($category) {
        'Biquínis', 'Maiôs', 'Sungas', 'Roupas de Banho' => 'Indicada para praia, piscina, férias e momentos de lazer ao ar livre.',
        'Bermudas', 'Shorts e Saias' => 'Indicada para dias quentes, passeios, escola, viagens e momentos de brincadeira.',
        'Blusas', 'Bodies' => 'Indicada para compor looks infantis confortáveis em passeios, escola e uso diário.',
        'Calças' => 'Indicada para uso diário, passeios e composições confortáveis em diferentes estações.',
        'Casacos' => 'Indicada para sobrepor blusas e camisetas em dias mais frescos com conforto.',
        'Conjuntos', 'Macacões' => 'Indicada para quem busca praticidade na hora de vestir, mantendo o visual coordenado.',
        'Pijamas' => 'Indicada para noites de sono e momentos de descanso com conforto.',
        'Vestidos' => 'Indicada para passeios, encontros em família e ocasiões em que um visual mais delicado combina com conforto.',
        default => 'Indicada para complementar o guarda-roupa infantil com praticidade e conforto.',
    };
}

function buildKeywords(string $name, string $category): string
{
    return implode(',', array_unique([
        'rataplam',
        'moda infantil',
        mb_strtolower($category),
        mb_strtolower($name),
    ]));
}

function normalizeText(string $value): string
{
    $value = mb_strtolower($value);
    $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

    return $converted !== false ? $converted : $value;
}
