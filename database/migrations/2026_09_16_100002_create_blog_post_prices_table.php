<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            // Free text ("Estados Unidos", "LATAM", "Europa"): the same entry can
            // carry one price per region, and several items within a region.
            $table->string('region', 120);
            $table->string('item', 150)->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('note', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['blog_post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_prices');
    }
};
