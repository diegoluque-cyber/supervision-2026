<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Falta;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $totalUnidades = Unidad::count();
        $activos = Unidad::where('estado', 'activo')->count();
        $mantenimiento = Unidad::where('estado', 'mantenimiento')->count();

        $stats = [
            'total_unidades' => $totalUnidades,
            'total_conductores' => Conductor::count(),
            'activos' => $activos,
            'mantenimiento' => $mantenimiento,
            'categorias' => [],
            'activos_pct' => $totalUnidades > 0 ? round(($activos / $totalUnidades) * 100) : 0,
            'mantenimiento_pct' => $totalUnidades > 0 ? round(($mantenimiento / $totalUnidades) * 100) : 0,
        ];

        foreach (array_keys(Unidad::CATEGORIAS) as $categoria) {
            $stats['categorias'][$categoria] = Unidad::where('categoria', $categoria)->count();
            $stats['faltas_categorias'][$categoria] = Falta::where('categoria', $categoria)->count();
        }

        // Calcular total de elementos restringidos (Lista Negra)
        $unidadesRestringidas = Unidad::where('estado', 'inactivo')
            ->orWhereHas('faltas', fn ($q) => $q->where('tipo', 'expulsado'))
            ->count();

        $conductoresRestringidos = Conductor::whereIn('estado', ['suspendido', 'inactivo'])
            ->orWhereHas('unidad.faltas', fn ($q) => $q->where('tipo', 'expulsado'))
            ->count();

        $stats['faltas_categorias']['lista_negra'] = $unidadesRestringidas + $conductoresRestringidos;

        $recentActivity = Falta::query()
            ->with(['unidad', 'conductor'])
            ->orderByDesc('fecha_falta')
            ->limit(5)
            ->get()
            ->map(fn (Falta $falta) => [
                'id' => $falta->id,
                'unidad_codigo' => $falta->unidad?->codigo ?? '—',
                'conductor' => $falta->conductor?->nombre ?? 'Sin conductor',
                'tipo' => $falta->tipo,
                'tipo_label' => $falta->tipo_label,
                'descripcion' => $falta->descripcion,
                'fecha' => $falta->fecha_falta?->diffForHumans() ?? '—',
            ]);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'categorias' => Unidad::CATEGORIAS,
            'recentActivity' => $recentActivity,
        ]);
    }
}
