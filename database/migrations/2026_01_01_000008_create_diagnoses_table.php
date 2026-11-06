<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disease_id')->constrained()->cascadeOnDelete();
            $table->decimal('confidence', 5, 2);
            // 1 = kesimpulan utama, 2+ = kandidat alternatif.
            $table->unsignedTinyInteger('rank');
            $table->timestamps();

            $table->unique(['consultation_session_id', 'disease_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
