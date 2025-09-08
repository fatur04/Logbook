<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('overtime_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->nullable();
            $table->string('initial');
            $table->string('nama');
            $table->string('bulan'); // format YYYY-MM
            $table->decimal('total_jam', 8, 2)->default(0);
            $table->decimal('total_lembur', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_summaries');
    }
};
