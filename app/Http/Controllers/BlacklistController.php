<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlacklistController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        // 1. Unidades restringidas: Inactivas o con faltas de expulsión
        $unidadesQuery = Unidad::query()->with(['conductor', 'faltas']);

        if ($search !== '') {
            $unidadesQuery->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('placa', 'like', "%{$search}%")
                  ->orWhereHas('conductor', function ($cq) use ($search) {
                      $cq->where('nombre', 'like', "%{$search}%")
                         ->orWhere('dni', 'like', "%{$search}%");
                  });
            });
        }

        $unidades = $unidadesQuery->where(function ($q) {
                $q->where('estado', 'inactivo')
                  ->orWhereHas('faltas', fn ($fq) => $fq->whereIn('tipo', ['expulsado', 'bloqueo_aplicativo']));
            })
            ->get()
            ->map(fn (Unidad $u) => [
                'id' => $u->id,
                'codigo' => $u->codigo,
                'placa' => $u->placa,
                'estado' => $u->estado,
                'categoria_label' => $u->categoria_label,
                'conductor' => $u->conductor ? [
                    'nombre' => $u->conductor->nombre,
                    'dni' => $u->conductor->dni,
                ] : null,
                'motivo' => $u->faltas->where('tipo', 'bloqueo_aplicativo')->isNotEmpty()
                    ? 'Bloqueo de Aplicativo'
                    : ($u->faltas->where('tipo', 'expulsado')->isNotEmpty()
                        ? 'Unidad Expulsada (Falta Registrada)'
                        : 'Unidad Inactiva'),
                'detalles_falta' => $u->faltas->whereIn('tipo', ['expulsado', 'bloqueo_aplicativo'])->first()?->descripcion ?? '—',
            ]);

        // 2. Conductores restringidos: Suspendidos/inactivos o asociados a unidades expulsadas
        $conductoresQuery = Conductor::query()->with(['unidad', 'unidad.faltas']);

        if ($search !== '') {
            $conductoresQuery->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhereHas('unidad', function ($uq) use ($search) {
                      $uq->where('codigo', 'like', "%{$search}%")
                         ->orWhere('placa', 'like', "%{$search}%");
                  });
            });
        }

        $conductores = $conductoresQuery->where(function ($q) {
                $q->whereIn('estado', ['suspendido', 'inactivo'])
                  ->orWhereHas('unidad.faltas', fn ($fq) => $fq->whereIn('tipo', ['expulsado', 'bloqueo_aplicativo']));
            })
            ->get()
            ->map(fn (Conductor $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'dni' => $c->dni,
                'telefono' => $c->telefono,
                'estado' => $c->estado,
                'unidad_codigo' => $c->unidad?->codigo ?? '—',
                'motivo' => $c->unidad?->faltas->where('tipo', 'bloqueo_aplicativo')->isNotEmpty()
                    ? 'Bloqueo de Aplicativo (Unidad)'
                    : (in_array($c->estado, ['suspendido', 'inactivo'])
                        ? 'Conductor ' . ucfirst($c->estado)
                        : 'Asociado a Unidad Expulsada'),
                'detalles_falta' => $c->unidad?->faltas->whereIn('tipo', ['expulsado', 'bloqueo_aplicativo'])->first()?->descripcion ?? '—',
            ]);

        return Inertia::render('Blacklist/Index', [
            'unidades' => $unidades,
            'conductores' => $conductores,
            'filters' => [
                'search' => $search ?: null,
            ],
            'stats' => [
                'total_restringidos' => $unidades->count() + $conductores->count(),
                'total_unidades' => $unidades->count(),
                'total_conductores' => $conductores->count(),
            ]
        ]);
    }
}
