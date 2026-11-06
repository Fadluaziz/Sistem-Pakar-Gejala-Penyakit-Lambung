<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->after('email');
            $table->date('birth_date')->nullable()->after('nik');
            $table->string('gender')->nullable()->after('birth_date');
            $table->string('phone', 20)->nullable()->after('gender');
            $table->text('address')->nullable()->after('phone');
            $table->string('occupation')->nullable()->after('address');
            $table->string('blood_type', 3)->nullable()->after('occupation');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik', 'birth_date', 'gender', 'phone', 'address', 'occupation', 'blood_type']);
        });
    }
};
