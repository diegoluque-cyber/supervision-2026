<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\Falta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConductorController extends Controller
{
    public function index(Request $request): Response
    {
        $search = ltrim($request->string('search')->trim()->toString(), '#');
        $estado = $request->string('estado')->toString();

        $query = Conductor::query()->with('unidad');

        if ($search !== '') {
            $searchUpper = strtoupper(trim($search));

            // Primero intentar coincidencia exacta de unidad
            $tieneUnidadExacta = \App\Models\Unidad::whereRaw('UPPER(codigo) = ?', [$searchUpper])->exists();

            if ($tieneUnidadExacta) {
                // Si existe esa unidad exacta, mostrar SOLO ese conductor
                $query->whereHas('unidad', function ($uq) use ($searchUpper) {
                    $uq->whereRaw('UPPER(codigo) = ?', [$searchUpper]);
                });
            } else {
                // Si no existe como unidad, buscar por nombre o DNI
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('dni', 'like', "%{$search}%");
                });
            }
        }

        if (in_array($estado, ['activo', 'inactivo', 'suspendido'], true)) {
            $query->where('estado', $estado);
        }

        $dbDriver = \Illuminate\Support\Facades\DB::getDriverName();

        if ($dbDriver === 'mysql') {
            $query->orderByRaw('(SELECT codigo FROM unidades WHERE unidades.conductor_id = conductores.id LIMIT 1) IS NULL ASC')
                ->orderByRaw('CAST((SELECT REGEXP_REPLACE(codigo, "[^0-9]", "") FROM unidades WHERE unidades.conductor_id = conductores.id LIMIT 1) AS UNSIGNED) ASC')
                ->orderBy('nombre');
        } else {
            $query->orderByRaw('(SELECT codigo FROM unidades WHERE unidades.conductor_id = conductores.id LIMIT 1) IS NULL ASC')
                ->orderByRaw('(SELECT codigo FROM unidades WHERE unidades.conductor_id = conductores.id LIMIT 1) ASC')
                ->orderBy('nombre');
        }

        return Inertia::render('Conductores/Index', [
            'conductores' => $query->paginate(30)->withQueryString(),
            'filters' => [
                'search' => $search ?: null,
                'estado' => $estado ?: null,
            ],
            'stats' => [
                'total'      => Conductor::count(),
                'activos'    => Conductor::where('estado', 'activo')->count(),
                'inactivos'  => Conductor::where('estado', 'inactivo')->count(),
                'suspendidos'=> Conductor::where('estado', 'suspendido')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'fecha_licencia_vencimiento' => $request->filled('fecha_licencia_vencimiento') ? $request->input('fecha_licencia_vencimiento') : null,
            'fecha_citacion'             => $request->filled('fecha_citacion') ? $request->input('fecha_citacion') : null,
            'motivo_citacion'            => $request->filled('motivo_citacion') ? $request->input('motivo_citacion') : null,
        ]);

        $data = $request->validate([
            'nombre'                     => ['required', 'string', 'max:120'],
            'dni'                        => ['required', 'string', 'max:15', 'unique:conductores,dni'],
            'licencia'                   => ['nullable', 'string', 'max:20'],
            'fecha_licencia_vencimiento' => ['nullable', 'date'],
            'telefono'                   => ['nullable', 'string', 'max:20'],
            'estado'                     => ['required', 'in:activo,inactivo,suspendido'],
            'fecha_citacion'             => ['nullable', 'date'],
            'motivo_citacion'            => ['nullable', 'string', 'max:255'],
        ]);

        Conductor::create($data);

        return back()->with('success', 'Conductor agregado correctamente.');
    }

    public function update(Request $request, Conductor $conductor): RedirectResponse
    {
        $request->merge([
            'fecha_licencia_vencimiento' => $request->filled('fecha_licencia_vencimiento') ? $request->input('fecha_licencia_vencimiento') : null,
            'fecha_citacion'             => $request->filled('fecha_citacion') ? $request->input('fecha_citacion') : null,
            'motivo_citacion'            => $request->filled('motivo_citacion') ? $request->input('motivo_citacion') : null,
        ]);

        $data = $request->validate([
            'nombre'                     => ['required', 'string', 'max:120'],
            'dni'                        => ['required', 'string', 'max:15', 'unique:conductores,dni,' . $conductor->id],
            'licencia'                   => ['nullable', 'string', 'max:20'],
            'fecha_licencia_vencimiento' => ['nullable', 'date'],
            'telefono'                   => ['nullable', 'string', 'max:20'],
            'estado'                     => ['required', 'in:activo,inactivo,suspendido'],
            'fecha_citacion'             => ['nullable', 'date'],
            'motivo_citacion'            => ['nullable', 'string', 'max:255'],
        ]);

        $conductor->update($data);

        return back()->with('success', 'Conductor actualizado correctamente.');
    }

    public function destroy(Conductor $conductor): RedirectResponse
    {
        // Desvincular unidad antes de eliminar
        if ($conductor->unidad) {
            $conductor->unidad->update(['conductor_id' => null]);
        }

        $conductor->delete();

        return back()->with('success', 'Conductor eliminado.');
    }

    public function destroyMultiple(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:conductores,id'],
        ]);

        $conductores = Conductor::whereIn('id', $data['ids'])->get();

        foreach ($conductores as $conductor) {
            if ($conductor->unidad) {
                $conductor->unidad->update(['conductor_id' => null]);
            }
            $conductor->delete();
        }

        $count = count($data['ids']);
        return back()->with('success', "{$count} conductor(es) eliminado(s).");
    }

    public function printReport(Request $request)
    {
        $search = ltrim($request->string('search')->trim()->toString(), '#');
        $estado = $request->string('estado')->toString();

        $query = Conductor::query()->with('unidad');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhereHas('unidad', function ($uq) use ($search) {
                      $uq->where('codigo', $search)->orWhere('placa', 'like', "%{$search}%");
                  });
            });
        }

        if (in_array($estado, ['activo', 'inactivo', 'suspendido'], true)) {
            $query->where('estado', $estado);
        }

        $conductores = $query->orderBy('nombre')->get();

        return view('exports.conductores_print', compact('conductores', 'estado', 'search'));
    }

    public function quitarCitacion(Conductor $conductor): RedirectResponse
    {
        $conductor->update([
            'fecha_citacion' => null,
            'motivo_citacion' => null,
        ]);

        // También marcar como leídas las alarmas de retorno asociadas a este conductor
        Falta::where('conductor_id', $conductor->id)
            ->where('alarma_leida', false)
            ->update(['alarma_leida' => true]);

        return back()->with('success', "Citación de {$conductor->nombre} removida y alarmas asociadas marcadas como leídas.");
    }
}
