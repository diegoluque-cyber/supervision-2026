<?php

namespace Database\Seeders;

use App\Models\Conductor;
use App\Models\Falta;
use App\Models\Papeleta;
use App\Models\Unidad;
use Illuminate\Database\Seeder;

class TestContentSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar faltas y papeletas de prueba
        Falta::truncate();
        Papeleta::truncate();

        // Limpiar citaciones y licencias de conductores anteriores
        Conductor::query()->update([
            'fecha_licencia_vencimiento' => null,
            'fecha_citacion' => null,
            'motivo_citacion' => null,
        ]);

        // Unidades y Conductores
        $unidad1 = Unidad::find(1); // P119
        $unidad2 = Unidad::find(2); // P118
        $unidad3 = Unidad::find(3); // 602
        $unidad4 = Unidad::find(4); // 594
        $unidad5 = Unidad::find(5); // 1249

        // 1. Asignar categorías de prueba a estas unidades (para demostrar categorías)
        if ($unidad1) $unidad1->update(['categoria' => 'san_pablo']);
        if ($unidad2) $unidad2->update(['categoria' => 'cerro_verde']);
        if ($unidad3) $unidad3->update(['categoria' => 'mitsui']);
        if ($unidad4) $unidad4->update(['categoria' => 'conductor']);
        // unidad 5 queda como 'conductor'

        // 2. Crear Faltas
        if ($unidad1) {
            Falta::create([
                'unidad_id' => $unidad1->id,
                'conductor_id' => $unidad1->conductor_id,
                'tipo' => 'observacion',
                'categoria' => 'san_pablo',
                'descripcion' => 'Conductor fue visto cargando combustible con pasajeros a bordo.',
                'fotos' => [],
                'supervisor' => 'Supervisor Alo 45',
                'fecha_falta' => now()->subDay(),
            ]);
        }

        if ($unidad2) {
            Falta::create([
                'unidad_id' => $unidad2->id,
                'conductor_id' => $unidad2->conductor_id,
                'tipo' => 'falta_grave',
                'categoria' => 'cerro_verde',
                'descripcion' => 'Exceso de velocidad registrado por GPS en zona urbana (Cerro Verde).',
                'fotos' => [],
                'supervisor' => 'Supervisor Alo 45',
                'fecha_falta' => now()->subDays(2),
            ]);
        }

        if ($unidad5) {
            Falta::create([
                'unidad_id' => $unidad5->id,
                'conductor_id' => $unidad5->conductor_id,
                'tipo' => 'observacion',
                'categoria' => 'conductor',
                'descripcion' => 'Unidad sucia durante la inspección interna de rutina.',
                'fotos' => [],
                'supervisor' => 'Supervisor Alo 45',
                'fecha_falta' => now(),
            ]);
        }

        // 3. Crear Alarma de Falta programada para Hoy (para disparar notificación)
        if ($unidad3) {
            Falta::create([
                'unidad_id' => $unidad3->id,
                'conductor_id' => $unidad3->conductor_id,
                'tipo' => 'observacion',
                'categoria' => 'mitsui',
                'descripcion' => 'Alarma programada: Control de revisión técnica de la unidad.',
                'fotos' => [],
                'supervisor' => 'Supervisor Alo 45',
                'fecha_falta' => now(),
                'fecha_alarma' => now()->toDateString(),
                'alarma_leida' => false,
            ]);
        }

        // 4. Configurar Alarma de Licencia a punto de vencer (en 5 días)
        if ($unidad1 && $unidad1->conductor) {
            $unidad1->conductor->update([
                'licencia' => 'Q7384920',
                'fecha_licencia_vencimiento' => now()->addDays(5)->toDateString(),
            ]);
        }

        // 5. Configurar Alarma de Conductor Citado para Hoy
        if ($unidad2 && $unidad2->conductor) {
            $unidad2->conductor->update([
                'fecha_citacion' => now()->toDateString(),
                'motivo_citacion' => 'Presentar documentos de SOAT y revisión técnica actualizados',
            ]);
        }

        // 6. Crear Papeletas de Prueba
        if ($unidad1) {
            Papeleta::create([
                'unidad_id' => $unidad1->id,
                'conductor_id' => $unidad1->conductor_id,
                'nro_papeleta' => 'M-12492',
                'monto' => 245.50,
                'fecha_emision' => now()->subDays(5)->toDateString(),
                'infraccion' => 'G-57 Estacionar en zona prohibida',
                'estado_pago' => 'pendiente',
            ]);
        }

        if ($unidad2) {
            Papeleta::create([
                'unidad_id' => $unidad2->id,
                'conductor_id' => $unidad2->conductor_id,
                'nro_papeleta' => 'M-88392',
                'monto' => 120.00,
                'fecha_emision' => now()->subDays(10)->toDateString(),
                'infraccion' => 'L-02 Luz de freno inoperativa',
                'estado_pago' => 'pagado',
                'fecha_pago' => now()->subDays(8)->toDateString(),
            ]);
        }
    }
}
