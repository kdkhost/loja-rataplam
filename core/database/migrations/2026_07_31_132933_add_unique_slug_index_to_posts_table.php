<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adiciona índice UNIQUE à coluna slug da tabela posts.
     * Antes de criar o índice, verifica se existem slugs duplicados,
     * nulos ou vazios que impediriam a operação.
     */
    public function up(): void
    {
        // Verificar slugs nulos
        $hasNulls = DB::table('posts')->whereNull('slug')->exists();
        if ($hasNulls) {
            throw new \RuntimeException(
                'Migration interrompida: existem posts com slug NULL. ' .
                'Corrija manualmente antes de aplicar o índice UNIQUE.'
            );
        }

        // Verificar slugs vazios ou compostos apenas por espaços
        $hasBlank = DB::table('posts')
            ->whereRaw('TRIM(slug) = ?', [''])
            ->exists();
        if ($hasBlank) {
            throw new \RuntimeException(
                'Migration interrompida: existem posts com slug vazio ou composto apenas por espaços. ' .
                'Corrija manualmente antes de aplicar o índice UNIQUE.'
            );
        }

        // Verificar slugs duplicados
        $hasDuplicates = DB::table('posts')
            ->select('slug', DB::raw('COUNT(*) as total'))
            ->groupBy('slug')
            ->having('total', '>', 1)
            ->exists();

        if ($hasDuplicates) {
            throw new \RuntimeException(
                'Migration interrompida: existem slugs duplicados na tabela posts. ' .
                'Corrija manualmente antes de aplicar o índice UNIQUE.'
            );
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->unique('slug', 'posts_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique('posts_slug_unique');
        });
    }
};
