<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converte a coluna posts.photo de VARCHAR(255) para TEXT NULL
     * de forma não destrutiva (sem apagar, reescrever ou normalizar posts).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `posts` MODIFY `photo` TEXT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * NOTA TÉCNICA: A reversão de TEXT para VARCHAR(255) NÃO é executada
     * automaticamente pois truncaria galerias com mais de 255 caracteres
     * de JSON, causando perda de dados irreversível.
     */
    public function down(): void
    {
        // Não reverte para VARCHAR(255) para evitar truncamento.
    }
};
