<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Region pricing moved to the plans section (program_region_prices), where
     * it drives what the buyer is actually charged. The blog keeps only its
     * content.
     */
    public function up(): void
    {
        Schema::dropIfExists('blog_post_prices');
    }

    public function down(): void
    {
        Schema::create('blog_post_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
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
};
