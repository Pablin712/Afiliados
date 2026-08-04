<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_buttons', function (Blueprint $table) {
            $table->id();
            $table->string('label_es', 100);
            $table->string('label_en', 100);
            // Internal anchor (#contacto), relative path, or full external URL.
            $table->string('url', 500);
            $table->string('style', 20)->default('primary');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_buttons');
    }
};
