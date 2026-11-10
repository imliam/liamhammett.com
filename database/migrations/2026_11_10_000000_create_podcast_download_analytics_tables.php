<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podcast_download_windows', function (Blueprint $table) {
            $table->string('fingerprint', 64)->primary();
            $table->unsignedBigInteger('counted_at')->index();
        });

        Schema::create('podcast_download_counts', function (Blueprint $table) {
            $table->id();
            $table->string('podcast_slug');
            $table->string('episode_guid');
            $table->date('date')->index();
            $table->unsignedBigInteger('requests')->default(0);
            $table->unique(['podcast_slug', 'episode_guid', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcast_download_counts');
        Schema::dropIfExists('podcast_download_windows');
    }
};
