<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Limpiar tablas si existen parcialmente
        Schema::dropIfExists('supervisor_attendance');
        Schema::dropIfExists('supervisor_meetings');

        // 1. Crear tabla de reuniones de supervisores
        Schema::create('supervisor_meetings', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('tema')->nullable();
            $table->timestamps();
        });

        // 2. Crear tabla de asistencias de supervisores
        Schema::create('supervisor_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('supervisor_meetings')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->enum('estado', ['asistio', 'tardanza', 'falta', 'justificado'])->default('asistio');
            $table->string('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_attendance');
        Schema::dropIfExists('supervisor_meetings');
    }
};
