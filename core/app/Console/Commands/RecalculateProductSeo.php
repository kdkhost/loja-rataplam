<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Services\Seo\ProductSeoAnalyzer;
use Illuminate\Console\Command;

class RecalculateProductSeo extends Command
{
    protected $signature = 'seo:recalculate-products {--chunk=100}';

    protected $description = 'Recalcula a pontuacao SEO de todos os produtos cadastrados.';

    public function handle(ProductSeoAnalyzer $analyzer): int
    {
        $count = 0;
        $chunk = max(25, (int) $this->option('chunk'));

        Item::query()->orderBy('id')->chunk($chunk, function ($items) use ($analyzer, &$count) {
            foreach ($items as $item) {
                $analysis = $analyzer->analyzeFromArray($item->toArray());
                $item->forceFill([
                    'seo_score' => $analysis['score'],
                    'seo_analysis' => $analysis['checks'],
                    'seo_last_analyzed_at' => now(),
                ])->save();
                $count++;
            }
        });

        $this->info($count . ' produtos recalculados.');

        return self::SUCCESS;
    }
}
