<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 200)->unique();
            $table->string('title_es', 200);
            // English copy is optional: when empty the Spanish one is shown,
            // so the admin can publish without translating every entry.
            $table->string('title_en', 200)->nullable();
            $table->text('excerpt_es')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->longText('body_es');
            $table->longText('body_en')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
