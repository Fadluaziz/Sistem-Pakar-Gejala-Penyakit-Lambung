<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            // Dokter yang memvalidasi hasil konsultasi pasien.
            $table->foreignId('doctor_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();

            // 'correct'  = kesimpulan sistem sesuai penilaian dokter
            // 'incorrect' = kesimpulan sistem kurang sesuai
            $table->string('validation')->nullable()->after('has_red_flag');
            $table->string('doctor_note')->nullable()->after('validation');
            $table->timestamp('validated_at')->nullable()->after('doctor_note');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropColumn(['validation', 'doctor_note', 'validated_at']);
        });
    }
};