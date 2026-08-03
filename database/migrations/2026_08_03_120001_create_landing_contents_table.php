<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150);
            $table->string('locale', 10);
            $table->string('type', 20)->default('text');
            $table->string('group', 60);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['key', 'locale']);
            $table->index(['group', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_contents');
    }
};
