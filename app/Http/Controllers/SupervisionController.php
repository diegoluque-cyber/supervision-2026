<?php

namespace App\Http\Controllers;

use App\Models\Falta;
use App\Models\Unidad;
use App\Models\Conductor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupervisionController extends Controller
{
    /**
     * Página principal de supervisión por categoría.
     */
    public function index(Request $request): Response
    {
        $categoria = $request->string('categoria')->toString() ?: 'supervision';
        $search    = $request->string('search')->trim()->toString();

        // Validar categoría
        if (! array_key_exists($categoria, Unidad::CATEGORIAS)) {
            $categoria = 'supervision';
        }

        // Buscar unidad por código exacto dentro de la categoría
        $unidadEncontrada = null;
        if ($search !== '') {
            $unidadEncontrada = Unidad::with(['conductor', 'faltas' => fn ($q) => $q->orderByDesc('fecha_falta')])
                ->where('categoria', $categoria)
                ->whereRaw('UPPER(codigo) = ?', [strtoupper($search)])
                ->first();

            // Si no encuentra en la categoría, buscar en todas
            if (! $unidadEncontrada) {
                $unidadEncontrada = Unidad::with(['conductor', 'faltas' => fn ($q) => $q->orderByDesc('fecha_falta')])
                    ->whereRaw('UPPER(codigo) = ?', [strtoupper($search)])
                    ->first();
            }
        }

        // Calcular reincidencia si hay unidad encontrada
        $recentGraveCount = 0;
        $isReincidente = false;
        if ($unidadEncontrada) {
            $recentGraveCount = Falta::where('tipo', 'falta_grave')
                ->where(function ($q) use ($unidadEncontrada) {
                    $q->where('unidad_id', $unidadEncontrada->id);
                    if ($unidadEncontrada->conductor_id) {
                        $q->orWhere('conductor_id', $unidadEncontrada->conductor_id);
                    }
                })
                ->where('fecha_falta', '>=', now()->subDays(30))
                ->count();
            $isReincidente = $recentGraveCount > 3;
        }

        // Últimas faltas de la categoría (historial general)
        $historial = Falta::with(['unidad', 'conductor'])
            ->where('categoria', $categoria)
            ->orderByDesc('fecha_falta')
            ->limit(30)
            ->get()
            ->map(fn (Falta $f) => [
                'id'              => $f->id,
                'unidad_codigo'   => $f->unidad->codigo ?? '—',
                'unidad_placa'    => $f->unidad->placa ?? '—',
                'unidad_categoria'=> $f->unidad->categoria ?? '',
                'unidad_categoria_label'=> $f->unidad->categoria_label ?? '',
                'conductor'       => $f->conductor->nombre ?? '—',
                'tipo'         => $f->tipo,
                'tipo_label'   => $f->tipo_label,
                'tipo_color'   => Falta::TIPO_COLORS[$f->tipo] ?? '',
                'descripcion'  => $f->descripcion,
                'fotos'        => $f->fotos ?? [],
                'supervisor'   => $f->supervisor,
                'fecha_falta'  => $f->fecha_falta->format('d/m/Y H:i'),
            ]);

        // Stats rápidas (filtradas por categoría activa)
        $stats = [];
        foreach (array_keys(Falta::TIPOS) as $tipo) {
            $stats[$tipo] = Falta::where('categoria', $categoria)->where('tipo', $tipo)->count();
        }

        return Inertia::render('Supervision/Index', [
            'categoria'        => $categoria,
            'categorias'       => Unidad::CATEGORIAS,
            'search'           => $search,
            'unidadEncontrada' => $unidadEncontrada ? [
                'id'              => $unidadEncontrada->id,
                'codigo'          => $unidadEncontrada->codigo,
                'placa'           => $unidadEncontrada->placa,
                'categoria'       => $unidadEncontrada->categoria,
                'categoria_label' => $unidadEncontrada->categoria_label,
                'estado'          => $unidadEncontrada->estado,
                'marca'     => $unidadEncontrada->marca,
                'modelo'    => $unidadEncontrada->modelo,
                'color'     => $unidadEncontrada->color,
                'anio'      => $unidadEncontrada->anio,
                'recent_grave_count' => $recentGraveCount,
                'is_reincidente'     => $isReincidente,
                'conductor' => $unidadEncontrada->conductor ? [
                    'id'      => $unidadEncontrada->conductor->id,
                    'nombre'  => $unidadEncontrada->conductor->nombre,
                    'dni'     => $unidadEncontrada->conductor->dni,
                    'telefono'=> $unidadEncontrada->conductor->telefono,
                ] : null,
                'faltas'    => $unidadEncontrada->faltas->map(fn (Falta $f) => [
                    'id'          => $f->id,
                    'tipo'        => $f->tipo,
                    'tipo_label'  => $f->tipo_label,
                    'tipo_color'  => Falta::TIPO_COLORS[$f->tipo] ?? '',
                    'descripcion' => $f->descripcion,
                    'fotos'       => $f->fotos ?? [],
                    'supervisor'  => $f->supervisor,
                    'fecha_falta' => $f->fecha_falta->format('d/m/Y H:i'),
                ])->values(),
            ] : null,
            'historial' => $historial,
            'stats'     => $stats,
            'tipos'     => Falta::TIPOS,
        ]);
    }

    /**
     * Registrar una falta a una unidad.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'unidad_id'   => ['required', 'exists:unidades,id'],
            'tipo'        => ['required', 'in:observacion,falta_grave,expulsado,tarifa_negociable,libre,cancela_servicios_corporativos,bloqueo_aplicativo'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'fotos'       => ['nullable', 'array'],
            'fotos.*'     => ['image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'categoria'   => ['nullable', 'string', 'in:' . implode(',', array_keys(Unidad::CATEGORIAS))],
        ]);

        $unidad = Unidad::findOrFail($data['unidad_id']);

        $fotos = [];
        if ($request->hasFile('fotos')) {
            $uploadPath = public_path('uploads/evidencias');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            foreach ($request->file('fotos') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);
                $fotos[] = 'uploads/evidencias/' . $filename;
            }
        }

        $categoria = $data['categoria'] ?? $unidad->categoria;

        if ($data['tipo'] === 'expulsado' || $data['tipo'] === 'bloqueo_aplicativo') {
            $unidad->update(['estado' => 'inactivo']);
            if ($unidad->conductor_id) {
                $conductor = Conductor::find($unidad->conductor_id);
                if ($conductor) {
                    $conductor->update(['estado' => 'suspendido']);
                }
            }
        } elseif ($data['tipo'] === 'libre') {
            // Eliminar cualquier falta de tipo 'expulsado' o 'bloqueo_aplicativo' para esta unidad y limpiar sus fotos
            $expulsadoFaltas = Falta::where('unidad_id', $unidad->id)->whereIn('tipo', ['expulsado', 'bloqueo_aplicativo'])->get();
            foreach ($expulsadoFaltas as $expFalta) {
                if (is_array($expFalta->fotos)) {
                    foreach ($expFalta->fotos as $fotoPath) {
                        $fullPath = public_path($fotoPath);
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                }
                $expFalta->delete();
            }

            // Poner la unidad y conductor como activos
            $unidad->update(['estado' => 'activo']);
            if ($unidad->conductor_id) {
                $conductor = Conductor::find($unidad->conductor_id);
                if ($conductor) {
                    $conductor->update(['estado' => 'activo']);
                }
            }
        }

        Falta::create([
            'unidad_id'    => $unidad->id,
            'conductor_id' => $unidad->conductor_id,
            'tipo'         => $data['tipo'],
            'categoria'    => $categoria,
            'descripcion'  => $data['descripcion'],
            'fotos'        => $fotos,
            'supervisor'   => auth()->user()->name,
            'fecha_falta'  => now(),
        ]);

        return redirect()->route('supervision.index', [
            'categoria' => $categoria,
            'search'    => $unidad->codigo,
        ])->with('success', 'Falta registrada correctamente.');
    }

    /**
     * Eliminar una falta.
     */
    public function destroyFalta(Request $request, Falta $falta): RedirectResponse
    {
        // Eliminar archivos físicos de fotos si existen
        if (is_array($falta->fotos)) {
            foreach ($falta->fotos as $fotoPath) {
                $fullPath = public_path($fotoPath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }

        $falta->delete();

        return redirect()->route('supervision.index', [
            'categoria' => $request->query('categoria', 'supervision'),
            'search'    => $request->query('search'),
        ])->with('success', 'Falta eliminada.');
    }

    /**
     * Exportar faltas a Excel.
     */
    public function exportExcel(Request $request)
    {
        $filters = [
            'categoria' => $request->string('categoria')->toString(),
            'search' => $request->string('search')->trim()->toString(),
            'fecha_desde' => $request->string('fecha_desde')->toString(),
            'fecha_hasta' => $request->string('fecha_hasta')->toString(),
        ];

        $filename = 'reporte_faltas_' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\FaltasExport($filters), $filename);
    }

    /**
     * Reporte imprimible (PDF del navegador).
     */
    public function printReport(Request $request)
    {
        $categoria = $request->string('categoria')->toString();
        $search = $request->string('search')->trim()->toString();
        $fecha_desde = $request->string('fecha_desde')->toString();
        $fecha_hasta = $request->string('fecha_hasta')->toString();

        $query = Falta::query()->with(['unidad', 'conductor']);

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('unidad', function ($uq) use ($search) {
                    $uq->where('codigo', $search)->orWhere('placa', 'like', "%{$search}%");
                })->orWhereHas('conductor', function ($cq) use ($search) {
                    $cq->where('nombre', 'like', "%{$search}%")->orWhere('dni', 'like', "%{$search}%");
                });
            });
        }

        if ($fecha_desde) {
            $query->whereDate('fecha_falta', '>=', $fecha_desde);
        }

        if ($fecha_hasta) {
            $query->whereDate('fecha_falta', '<=', $fecha_hasta);
        }

        $faltas = $query->orderByDesc('fecha_falta')->get();
        $categoriaLabel = Unidad::CATEGORIAS[$categoria] ?? 'General';

        return view('exports.faltas_print', compact('faltas', 'categoriaLabel', 'fecha_desde', 'fecha_hasta'));
    }
}
