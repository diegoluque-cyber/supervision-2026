<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('codigo')->unique();
            $table->string('placa', 10);
            $table->enum('categoria', [
                'supervision',
                'san_pablo',
                'cerro_verde',
                'mitsui',
                'otros',
            ]);
            $table->enum('estado', ['activo', 'inactivo', 'mantenimiento'])->default('activo');
            $table->foreignId('conductor_id')->nullable()->constrained('conductores')->nullOnDelete();
            $table->string('marca', 50)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->timestamps();

            $table->index('categoria');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
