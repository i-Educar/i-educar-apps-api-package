<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fila idempotente de sync professor (Onda 4/5).
 * Schema próprio apps_api.* — sem DROP SCHEMA CASCADE no down.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS apps_api');

        if (! Schema::hasTable('apps_api.professor_sync_evento')) {
            Schema::create('apps_api.professor_sync_evento', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('uuid', 64);
                $table->string('tipo', 32);
                $table->unsignedBigInteger('servidor_id')->nullable();
                $table->jsonb('payload');
                $table->string('status', 20)->default('aceito');
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('processed_at')->nullable();
                $table->unique('uuid');
                $table->index(['tipo', 'status']);
                $table->index('servidor_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('apps_api.professor_sync_evento');
    }
};
