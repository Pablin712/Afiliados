<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('membership_type_id')->constrained()->restrictOnDelete();
            $table->decimal('first_payment_cost', 12, 2)->default(0);
            $table->decimal('renewal_cost', 12, 2)->default(0);
            $table->unsignedTinyInteger('duration_months')->default(2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
