<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            // Jejak penalaran: premis mana saja yang menyumbang bobot ke penyakit ini
            // saat konsultasi dijalankan. Disimpan agar halaman hasil tetap setia pada
            // kondisi saat itu, walau bobot basis aturan diubah di kemudian hari.
            $table->json('breakdown')->nullable()->after('confidence');
            // Total bobot ciri penyakit ini pada saat konsultasi, dipakai sebagai
            // penyebut pada penjelasan perhitungan keyakinan.
            $table->unsignedSmallInteger('total_weight')->default(0)->after('breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('diagnoses', function (Blueprint $table) {
            $table->dropColumn(['breakdown', 'total_weight']);
        });
    }
};
