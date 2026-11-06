<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `description` awalnya NOT NULL dari scaffold, sementara summary / treatment /
     * prevention sudah nullable. Disamakan agar editor bisa menyimpan data penyakit
     * secara bertahap tanpa wajib mengisi seluruh kolom teks.
     */
    public function up(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
        });
    }
};
