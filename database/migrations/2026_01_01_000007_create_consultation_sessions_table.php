<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // diagnosed | inconclusive | insufficient
            $table->string('status')->default('inconclusive');
            $table->boolean('has_red_flag')->default(false);
            $table->timestamps();
        });

        // Pivot gejala yang dipilih pada satu sesi konsultasi.
        // Nama tabel dipendekkan agar nama index tetap di bawah batas 64 karakter MySQL.
        Schema::create('consultation_symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained()->cascadeOnDelete();

            $table->unique(['consultation_session_id', 'symptom_id'], 'session_symptom_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_symptoms');
        Schema::dropIfExists('consultation_sessions');
    }
};
