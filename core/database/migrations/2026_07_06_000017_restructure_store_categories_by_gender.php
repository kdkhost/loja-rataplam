<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $genderCategories = [
        'Masculina' => 1,
        'Feminina' => 2,
        'Unisex' => 3,
    ];

    private array $categoryGenderMap = [
        'Bermudas' => 'Masculina',
        'Sungas' => 'Masculina',
        'Biquínis' => 'Feminina',
        'Maiôs' => 'Feminina',
        'Shorts e Saias' => 'Feminina',
        'Vestidos' => 'Feminina',
        'Blusas' => 'Unisex',
        'Bodies' => 'Unisex',
        'Calças' => 'Unisex',
        'Casacos' => 'Unisex',
        'Conjuntos' => 'Unisex',
        'Macacões' => 'Unisex',
        'Pijamas' => 'Unisex',
        'Roupas de Banho' => 'Unisex',
        'Acessórios' => 'Unisex',
        'Enxoval' => 'Unisex',
        'Moda Infantil' => 'Unisex',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $genderIds = $this->ensureGenderCategories();
            $subcategoryIds = $this->ensureMappedSubcategories($genderIds);

            foreach ($this->categoryGenderMap as $legacyName => $genderName) {
                $legacyCategory = $this->findCategoryByName($legacyName);
                if (!$legacyCategory) {
                    continue;
                }

                DB::table('items')
                    ->where('category_id', $legacyCategory->id)
                    ->update([
                        'category_id' => $genderIds[$genderName],
                        'subcategory_id' => $subcategoryIds[$legacyName],
                    ]);

                DB::table('categories')
                    ->where('id', $legacyCategory->id)
                    ->update(['status' => 0]);
            }

            $this->updateHomeCustomize($genderIds, $subcategoryIds);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $genderIds = $this->genderCategoryIds();

            foreach ($this->categoryGenderMap as $legacyName => $genderName) {
                $legacyCategory = $this->findCategoryByName($legacyName);
                $subcategory = $this->findSubcategory($legacyName, $genderIds[$genderName] ?? null);

                if (!$legacyCategory || !$subcategory) {
                    continue;
                }

                DB::table('items')
                    ->where('category_id', $genderIds[$genderName])
                    ->where('subcategory_id', $subcategory->id)
                    ->update([
                        'category_id' => $legacyCategory->id,
                        'subcategory_id' => 0,
                    ]);

                DB::table('categories')
                    ->where('id', $legacyCategory->id)
                    ->update(['status' => 1]);
            }

            DB::table('categories')
                ->whereIn('name', array_keys($this->genderCategories))
                ->update(['status' => 0]);
        });
    }

    private function ensureGenderCategories(): array
    {
        $ids = [];

        foreach ($this->genderCategories as $name => $serial) {
            $slug = Str::slug($name);
            $existing = DB::table('categories')
                ->where('name', $name)
                ->orWhere('slug', $slug)
                ->first();

            if ($existing) {
                DB::table('categories')
                    ->where('id', $existing->id)
                    ->update([
                        'name' => $name,
                        'slug' => $slug,
                        'status' => 1,
                        'serial' => $serial,
                    ]);

                $ids[$name] = $existing->id;
                continue;
            }

            $ids[$name] = DB::table('categories')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'photo' => null,
                'status' => 1,
                'is_feature' => 1,
                'meta_keywords' => $name . ', moda infantil, rataplam',
                'meta_descriptions' => 'Produtos infantis da linha ' . $name . ' Rataplam.',
                'serial' => $serial,
            ]);
        }

        return $ids;
    }

    private function ensureMappedSubcategories(array $genderIds): array
    {
        $ids = [];

        foreach ($this->categoryGenderMap as $legacyName => $genderName) {
            $legacyCategory = $this->findCategoryByName($legacyName);
            if (!$legacyCategory) {
                continue;
            }

            $categoryId = $genderIds[$genderName];
            $slug = $legacyCategory->slug ?: Str::slug($legacyName);
            $existing = $this->findSubcategory($legacyName, $categoryId);

            if ($existing) {
                DB::table('subcategories')
                    ->where('id', $existing->id)
                    ->update([
                        'name' => $legacyName,
                        'slug' => $slug,
                        'category_id' => $categoryId,
                        'status' => 1,
                    ]);

                $ids[$legacyName] = $existing->id;
                continue;
            }

            $ids[$legacyName] = DB::table('subcategories')->insertGetId([
                'name' => $legacyName,
                'slug' => $slug,
                'category_id' => $categoryId,
                'status' => 1,
            ]);
        }

        return $ids;
    }

    private function updateHomeCustomize(array $genderIds, array $subcategoryIds): void
    {
        $home = DB::table('home_cutomizes')->first();
        if (!$home) {
            return;
        }

        DB::table('home_cutomizes')->where('id', $home->id)->update([
            'popular_category' => json_encode([
                'popular_title' => 'Categorias populares',
                'category_id1' => (string) $genderIds['Masculina'],
                'subcategory_id1' => null,
                'childcategory_id1' => null,
                'category_id2' => (string) $genderIds['Feminina'],
                'subcategory_id2' => null,
                'childcategory_id2' => null,
                'category_id3' => (string) $genderIds['Unisex'],
                'subcategory_id3' => null,
                'childcategory_id3' => null,
                'category_id4' => null,
                'subcategory_id4' => null,
                'childcategory_id4' => null,
            ], JSON_UNESCAPED_UNICODE),
            'feature_category' => json_encode([
                'feature_title' => 'Categorias em destaque',
                'category_id1' => (string) $genderIds['Masculina'],
                'subcategory_id1' => $subcategoryIds['Bermudas'] ?? null,
                'childcategory_id1' => null,
                'category_id2' => (string) $genderIds['Feminina'],
                'subcategory_id2' => $subcategoryIds['Vestidos'] ?? null,
                'childcategory_id2' => null,
                'category_id3' => (string) $genderIds['Unisex'],
                'subcategory_id3' => $subcategoryIds['Conjuntos'] ?? null,
                'childcategory_id3' => null,
                'category_id4' => null,
                'subcategory_id4' => null,
                'childcategory_id4' => null,
            ], JSON_UNESCAPED_UNICODE),
            'two_column_category' => json_encode([
                'category_id1' => (string) $genderIds['Masculina'],
                'subcategory_id1' => $subcategoryIds['Sungas'] ?? null,
                'childcategory_id1' => null,
                'category_id2' => (string) $genderIds['Feminina'],
                'subcategory_id2' => $subcategoryIds['Biquínis'] ?? null,
                'childcategory_id2' => null,
                'category_id3' => (string) $genderIds['Unisex'],
                'subcategory_id3' => $subcategoryIds['Pijamas'] ?? null,
                'childcategory_id3' => null,
            ], JSON_UNESCAPED_UNICODE),
            'home_4_popular_category' => json_encode(array_values($genderIds), JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function genderCategoryIds(): array
    {
        return DB::table('categories')
            ->whereIn('name', array_keys($this->genderCategories))
            ->pluck('id', 'name')
            ->toArray();
    }

    private function findCategoryByName(string $name): ?object
    {
        return DB::table('categories')
            ->where('name', $name)
            ->orWhere('slug', Str::slug($name))
            ->first();
    }

    private function findSubcategory(string $name, ?int $categoryId): ?object
    {
        if (!$categoryId) {
            return null;
        }

        return DB::table('subcategories')
            ->where('category_id', $categoryId)
            ->where(function ($query) use ($name) {
                $query->where('name', $name)
                    ->orWhere('slug', Str::slug($name));
            })
            ->first();
    }
};
