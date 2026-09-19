<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_region_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            // Free text ("EE.UU.", "LATAM", "Europa"): the buyer picks one of the
            // program's regions and is charged that row's costs instead of the
            // program's base ones.
            $table->string('region', 120);
            $table->decimal('first_payment_cost', 12, 2);
            $table->decimal('card_first_payment_cost', 12, 2)->nullable();
            $table->decimal('renewal_cost', 12, 2);
            $table->decimal('card_renewal_cost', 12, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['program_id', 'region']);
            $table->index(['program_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_region_prices');
    }
};
