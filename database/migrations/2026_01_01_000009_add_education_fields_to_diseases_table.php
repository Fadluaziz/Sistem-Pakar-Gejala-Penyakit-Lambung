<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            // Ringkasan singkat untuk halaman edukasi.
            $table->text('summary')->nullable()->after('name');
            // Dipisah agar halaman hasil bisa menampilkan keduanya terpisah.
            $table->text('treatment')->nullable()->after('description');
            $table->text('prevention')->nullable()->after('treatment');
        });

        // `solution` sebelumnya mencampur pengobatan dan pencegahan dalam satu kolom.
        Schema::table('diseases', function (Blueprint $table) {
            $table->dropColumn('solution');
        });
    }

    public function down(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->text('solution')->nullable()->after('description');
        });

        Schema::table('diseases', function (Blueprint $table) {
            $table->dropColumn(['summary', 'treatment', 'prevention']);
        });
    }
};
