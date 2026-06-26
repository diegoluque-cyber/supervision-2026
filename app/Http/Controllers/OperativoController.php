<?php

namespace App\Http\Controllers;

use App\Models\Operativo;
use App\Models\Falta;
use App\Models\Papeleta;
use App\Models\Unidad;
use App\Models\Conductor;
use App\Models\OperativoIntervencion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperativoController extends Controller
{
    /**
     * Listado de operativos.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $query = Operativo::withCount([
            'intervenciones as faltas_count' => function ($q) {
                $q->where('resultado', 'falta');
            },
            'intervenciones as papeletas_count' => function ($q) {
                $q->where('resultado', 'papeleta');
            }
        ]);

        if ($search !== '') {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereDate('fecha', $search);
        }

        $operativos = $query->orderByDesc('fecha')
                            ->paginate(15)
                            ->withQueryString();

        // Estadísticas rápidas para la vista de listado basadas en intervenciones
        $stats = [
            'total_operativos' => Operativo::count(),
            'unidades_reportadas_mes' => OperativoIntervencion::where('resultado', 'falta')
                                                              ->where('created_at', '>=', now()->startOfMonth())
                                                              ->count(),
            'papeletas_mes' => OperativoIntervencion::where('resultado', 'papeleta')
                                                    ->where('created_at', '>=', now()->startOfMonth())
                                                    ->count(),
        ];

        return Inertia::render('Operativos/Index', [
            'operativos' => $operativos,
            'filters' => [
                'search' => $search ?: null,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Registrar un nuevo operativo.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:255'],
            'fecha'       => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ]);

        Operativo::create($data);

        return redirect()->route('operativos.index')
                         ->with('success', 'Operativo registrado correctamente.');
    }

    /**
     * Ver detalle de un operativo.
     */
    public function show(Operativo $operativo): Response
    {
        // Cargar las relaciones detalladas
        $operativo->load([
            'faltas' => fn($q) => $q->with(['unidad', 'conductor'])->orderByDesc('fecha_falta'),
            'papeletas' => fn($q) => $q->with(['unidad', 'conductor'])->orderByDesc('fecha_emision'),
            'intervenciones' => fn($q) => $q->with(['unidad', 'conductor'])->orderByDesc('created_at'),
        ]);

        // Mapear faltas para coincidir con la visualización del sistema
        $faltas = $operativo->faltas->map(fn(Falta $f) => [
            'id'              => $f->id,
            'unidad_codigo'   => $f->unidad->codigo ?? '—',
            'unidad_placa'    => $f->unidad->placa ?? '—',
            'unidad_categoria'=> $f->unidad->categoria ?? '',
            'conductor'       => $f->conductor->nombre ?? '—',
            'tipo'            => $f->tipo,
            'tipo_label'      => $f->tipo_label,
            'tipo_color'      => Falta::TIPO_COLORS[$f->tipo] ?? '',
            'descripcion'     => $f->descripcion,
            'fotos'           => $f->fotos ?? [],
            'supervisor'      => $f->supervisor,
            'fecha_falta'     => $f->fecha_falta->format('d/m/Y H:i'),
        ]);

        // Mapear papeletas para coincidir con el formato de caja
        $papeletas = $operativo->papeletas->map(fn(Papeleta $p) => [
            'id'            => $p->id,
            'unidad_codigo' => $p->unidad->codigo ?? '—',
            'unidad_placa'  => $p->unidad->placa ?? '—',
            'conductor'     => $p->conductor->nombre ?? '—',
            'nro_papeleta'  => $p->nro_papeleta,
            'monto'         => $p->monto,
            'fecha_emision' => $p->fecha_emision->format('d/m/Y'),
            'infraccion'    => $p->infraccion,
            'estado_pago'   => $p->estado_pago,
            'fecha_pago'    => $p->fecha_pago ? $p->fecha_pago->format('d/m/Y') : null,
        ]);

        // Mapear intervenciones en el operativo
        $intervenciones = $operativo->intervenciones->map(fn(OperativoIntervencion $i) => [
            'id'                 => $i->id,
            'unidad_id'          => $i->unidad_id,
            'unidad_codigo'      => $i->unidad->codigo ?? '—',
            'unidad_placa'       => $i->unidad->placa ?? '—',
            'conductor_id'       => $i->conductor_id,
            'conductor'          => $i->conductor->nombre ?? 'Sin conductor',
            'resultado'          => $i->resultado,
            'observaciones'      => $i->observaciones ?? 'Sin observaciones',
            'fecha_intervencion' => $i->created_at->format('d/m/Y H:i'),
        ]);

        // Unidades con conductor para los formularios de registro rápido
        $unidades = Unidad::with('conductor')->orderBy('codigo')->get()->map(fn($u) => [
            'id'               => $u->id,
            'codigo'           => $u->codigo,
            'placa'            => $u->placa,
            'conductor_nombre' => $u->conductor?->nombre ?? 'Sin conductor',
        ]);

        return Inertia::render('Operativos/Show', [
            'operativo' => [
                'id'          => $operativo->id,
                'nombre'      => $operativo->nombre,
                'fecha'       => $operativo->fecha->format('Y-m-d\TH:i'), // Formato para datetime-local
                'fecha_label' => $operativo->fecha->format('d/m/Y H:i'),
                'descripcion' => $operativo->descripcion,
            ],
            'faltas'         => $faltas,
            'papeletas'      => $papeletas,
            'intervenciones' => $intervenciones,
            'unidades'       => $unidades,
            'tipos_faltas'   => Falta::TIPOS,
        ]);
    }

    /**
     * Actualizar los datos básicos del operativo.
     */
    public function update(Request $request, Operativo $operativo): RedirectResponse
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:255'],
            'fecha'       => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
        ]);

        $operativo->update($data);

        return back()->with('success', 'Operativo actualizado correctamente.');
    }

    /**
     * Eliminar el operativo.
     */
    public function destroy(Operativo $operativo): RedirectResponse
    {
        $operativo->delete();

        return redirect()->route('operativos.index')
                         ->with('success', 'Operativo eliminado.');
    }

    /**
     * Registrar una falta desde el detalle del operativo.
     */
    public function storeFalta(Request $request, Operativo $operativo): RedirectResponse
    {
        $data = $request->validate([
            'unidad_id'    => ['required', 'exists:unidades,id'],
            'tipo'         => ['required', 'in:observacion,falta_grave,expulsado,tarifa_negociable,libre,cancela_servicios_corporativos,bloqueo_aplicativo'],
            'descripcion'  => ['required', 'string', 'max:1000'],
            'fotos'        => ['nullable', 'array'],
            'fotos.*'      => ['image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'fecha_falta'  => ['nullable', 'date'],
            'fecha_alarma' => ['nullable', 'date'],
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

        $categoria = $unidad->categoria;

        // Lógica de estado de Unidad y Conductor
        if ($data['tipo'] === 'expulsado' || $data['tipo'] === 'bloqueo_aplicativo') {
            $unidad->update(['estado' => 'inactivo']);
            if ($unidad->conductor_id) {
                $conductor = Conductor::find($unidad->conductor_id);
                if ($conductor) {
                    $conductor->update(['estado' => 'suspendido']);
                }
            }
        } elseif ($data['tipo'] === 'libre') {
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

            $unidad->update(['estado' => 'activo']);
            if ($unidad->conductor_id) {
                $conductor = Conductor::find($unidad->conductor_id);
                if ($conductor) {
                    $conductor->update(['estado' => 'activo']);
                }
            }
        }

        $falta = Falta::create([
            'unidad_id'    => $unidad->id,
            'conductor_id' => $unidad->conductor_id,
            'tipo'         => $data['tipo'],
            'categoria'    => $categoria,
            'descripcion'  => $data['descripcion'],
            'fotos'        => $fotos,
            'supervisor'   => auth()->user()->name,
            'fecha_falta'  => $data['fecha_falta'] ?? now(),
            'fecha_alarma' => $data['fecha_alarma'] ?? null,
            'alarma_leida' => false,
            'operativo_id' => $operativo->id,
        ]);

        if (!empty($data['fecha_alarma']) && $unidad->conductor_id) {
            $conductor = Conductor::find($unidad->conductor_id);
            if ($conductor) {
                $conductor->update([
                    'fecha_citacion'  => $data['fecha_alarma'],
                    'motivo_citacion' => $data['descripcion'],
                ]);
            }
        }

        // Registrar o actualizar la intervención en el operativo
        OperativoIntervencion::updateOrCreate(
            ['operativo_id' => $operativo->id, 'unidad_id' => $unidad->id],
            [
                'conductor_id' => $unidad->conductor_id,
                'resultado'    => 'falta',
                'observaciones'=> $data['descripcion'],
            ]
        );

        return back()->with('success', 'Falta registrada en el operativo correctamente.');
    }

    /**
     * Registrar una papeleta desde el detalle del operativo.
     */
    public function storePapeleta(Request $request, Operativo $operativo): RedirectResponse
    {
        $data = $request->validate([
            'unidad_id'     => ['required', 'exists:unidades,id'],
            'nro_papeleta'  => ['required', 'string', 'max:50'],
            'monto'         => ['required', 'numeric', 'min:0'],
            'fecha_emision' => ['required', 'date'],
            'infraccion'    => ['nullable', 'string', 'max:255'],
            'estado_pago'   => ['required', 'in:pendiente,pagado'],
        ]);

        $unidad = Unidad::findOrFail($data['unidad_id']);

        Papeleta::create([
            'unidad_id'    => $unidad->id,
            'conductor_id' => $unidad->conductor_id,
            'nro_papeleta' => $data['nro_papeleta'],
            'monto'        => $data['monto'],
            'fecha_emision'=> $data['fecha_emision'],
            'infraccion'   => $data['infraccion'],
            'estado_pago'  => $data['estado_pago'],
            'fecha_pago'   => $data['estado_pago'] === 'pagado' ? now() : null,
            'operativo_id' => $operativo->id,
        ]);

        // Registrar o actualizar la intervención en el operativo
        OperativoIntervencion::updateOrCreate(
            ['operativo_id' => $operativo->id, 'unidad_id' => $unidad->id],
            [
                'conductor_id' => $unidad->conductor_id,
                'resultado'    => 'papeleta',
                'observaciones'=> $data['infraccion'],
            ]
        );

        return back()->with('success', 'Papeleta registrada en el operativo correctamente.');
    }

    /**
     * Desvincular una falta del operativo.
     */
    public function unlinkFalta(Falta $falta): RedirectResponse
    {
        $falta->update(['operativo_id' => null]);
        return back()->with('success', 'Falta desvinculada del operativo.');
    }

    /**
     * Desvincular una papeleta del operativo.
     */
    public function unlinkPapeleta(Papeleta $papeleta): RedirectResponse
    {
        $papeleta->update(['operativo_id' => null]);
        return back()->with('success', 'Papeleta desvinculada del operativo.');
    }

    /**
     * Eliminar permanentemente una falta desde el operativo.
     */
    public function destroyFalta(Falta $falta): RedirectResponse
    {
        if (is_array($falta->fotos)) {
            foreach ($falta->fotos as $fotoPath) {
                $fullPath = public_path($fotoPath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }
        $falta->delete();
        return back()->with('success', 'Falta eliminada permanentemente.');
    }

    /**
     * Eliminar permanentemente una papeleta desde el operativo.
     */
    public function destroyPapeleta(Papeleta $papeleta): RedirectResponse
    {
        $papeleta->delete();
        return back()->with('success', 'Papeleta de multa eliminada permanentemente.');
    }

    /**
     * Registrar una intervención de unidad en el operativo.
     */
    public function storeIntervencion(Request $request, Operativo $operativo): RedirectResponse
    {
        $data = $request->validate([
            'unidad_id'     => ['required', 'exists:unidades,id'],
            'resultado'     => ['required', 'in:sin_novedad,falta,papeleta'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $unidad = Unidad::findOrFail($data['unidad_id']);

        OperativoIntervencion::updateOrCreate(
            ['operativo_id' => $operativo->id, 'unidad_id' => $unidad->id],
            [
                'conductor_id' => $unidad->conductor_id,
                'resultado'    => $data['resultado'],
                'observaciones'=> $data['observaciones'],
            ]
        );

        return back()->with('success', 'Unidad registrada en el operativo correctamente.');
    }

    /**
     * Actualizar una intervención.
     */
    public function updateIntervencion(Request $request, OperativoIntervencion $intervencion): RedirectResponse
    {
        $data = $request->validate([
            'resultado'     => ['required', 'in:sin_novedad,falta,papeleta'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $intervencion->update($data);

        return back()->with('success', 'Intervención de unidad actualizada correctamente.');
    }

    /**
     * Eliminar una intervención del operativo.
     */
    public function destroyIntervencion(OperativoIntervencion $intervencion): RedirectResponse
    {
        $intervencion->delete();
        return back()->with('success', 'Unidad removida de la lista del operativo.');
    }
}
