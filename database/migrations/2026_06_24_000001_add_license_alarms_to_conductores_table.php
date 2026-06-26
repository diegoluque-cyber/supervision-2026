<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            $table->date('fecha_licencia_vencimiento')->nullable()->after('licencia');
            $table->date('fecha_citacion')->nullable()->after('estado');
            $table->string('motivo_citacion', 255)->nullable()->after('fecha_citacion');
        });
    }

    public function down(): void
    {
        Schema::table('conductores', function (Blueprint $table) {
            $table->dropColumn(['fecha_licencia_vencimiento', 'fecha_citacion', 'motivo_citacion']);
        });
    }
};
