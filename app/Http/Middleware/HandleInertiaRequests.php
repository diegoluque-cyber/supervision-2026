<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Conductor;
use App\Models\Falta;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'alarms' => function () use ($request) {
                if (!$request->user()) {
                    return [
                        'licencias' => [],
                        'citados' => [],
                        'faltas' => [],
                        'total' => 0
                    ];
                }

                $hoy = now()->toDateString();

                $citados = Conductor::whereNotNull('fecha_citacion')
                    ->where('fecha_citacion', '<=', $hoy)
                    ->with('unidad')
                    ->get()
                    ->map(fn ($c) => [
                        'id' => $c->id,
                        'nombre' => $c->nombre,
                        'fecha_citacion' => $c->fecha_citacion->format('d/m/Y'),
                        'motivo' => $c->motivo_citacion,
                        'unidad_codigo' => $c->unidad->codigo ?? null,
                    ]);

                $faltas = Falta::whereNotNull('fecha_alarma')
                    ->where('fecha_alarma', '<=', $hoy)
                    ->where('alarma_leida', false)
                    ->with(['unidad', 'conductor'])
                    ->get()
                    ->map(fn ($f) => [
                        'id' => $f->id,
                        'unidad_codigo' => $f->unidad->codigo ?? '—',
                        'conductor_nombre' => $f->conductor->nombre ?? 'Sin conductor',
                        'descripcion' => $f->descripcion,
                        'fecha_alarma' => $f->fecha_alarma->format('d/m/Y'),
                        'tipo_label' => $f->tipo_label,
                    ]);

                return [
                    'licencias' => [],
                    'citados' => $citados,
                    'faltas' => $faltas,
                    'total' => count($citados) + count($faltas)
                ];
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'importPreview' => fn () => $request->session()->get('importPreview'),
                'importPath' => fn () => $request->session()->get('importPath'),
                'importFileName' => fn () => $request->session()->get('importFileName'),
                'importResult' => fn () => $request->session()->get('importResult'),
            ],
        ];
    }
}
