<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS apps_api');

        Schema::create('apps_api.professor_sync_evento', function (Blueprint $table): void {
            $table->id();
            $table->string('uuid', 64)->unique();
            $table->string('tipo', 32);
            $table->unsignedBigInteger('servidor_id')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('aceito');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->index(['tipo', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apps_api.professor_sync_evento');
        DB::statement('DROP SCHEMA IF EXISTS apps_api CASCADE');
    }
};
