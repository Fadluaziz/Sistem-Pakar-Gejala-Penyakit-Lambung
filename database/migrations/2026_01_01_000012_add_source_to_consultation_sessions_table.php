<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            // Asal sesi: 'patient' (konsultasi pasien) atau 'manual' (diagnosa
            // yang ditambahkan langsung oleh dokter).
            $table->string('source')->default('patient')->after('has_red_flag');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};