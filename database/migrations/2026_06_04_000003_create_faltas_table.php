<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faltas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('conductor_id')->nullable()->constrained('conductores')->nullOnDelete();
            $table->enum('tipo', ['observacion', 'falta_grave', 'expulsado', 'libre'])->default('observacion');
            $table->text('descripcion');
            $table->string('supervisor')->nullable(); // nombre del supervisor que registró
            $table->timestamp('fecha_falta')->useCurrent();
            $table->timestamps();

            $table->index('unidad_id');
            $table->index('tipo');
            $table->index('fecha_falta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faltas');
    }
};
