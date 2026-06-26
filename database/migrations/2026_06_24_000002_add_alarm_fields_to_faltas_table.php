<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faltas', function (Blueprint $table) {
            $table->date('fecha_alarma')->nullable()->after('fecha_falta');
            $table->boolean('alarma_leida')->default(false)->after('fecha_alarma');
        });
    }

    public function down(): void
    {
        Schema::table('faltas', function (Blueprint $table) {
            $table->dropColumn(['fecha_alarma', 'alarma_leida']);
        });
    }
};
