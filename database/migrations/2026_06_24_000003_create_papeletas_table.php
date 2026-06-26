<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('papeletas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidad_id')->constrained('unidades')->cascadeOnDelete();
            $table->foreignId('conductor_id')->nullable()->constrained('conductores')->nullOnDelete();
            $table->string('nro_papeleta', 50);
            $table->decimal('monto', 10, 2);
            $table->date('fecha_emision');
            $table->string('infraccion', 255)->nullable();
            $table->enum('estado_pago', ['pendiente', 'pagado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->timestamps();

            $table->index('unidad_id');
            $table->index('estado_pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('papeletas');
    }
};
