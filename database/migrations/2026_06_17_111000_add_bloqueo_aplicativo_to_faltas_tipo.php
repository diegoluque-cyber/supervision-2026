<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE faltas MODIFY COLUMN tipo ENUM('observacion','falta_grave','expulsado','tarifa_negociable','libre','cancela_servicios_corporativos','bloqueo_aplicativo') NOT NULL DEFAULT 'observacion'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE faltas MODIFY COLUMN tipo ENUM('observacion','falta_grave','expulsado','tarifa_negociable','libre','cancela_servicios_corporativos') NOT NULL DEFAULT 'observacion'");
    }
};
