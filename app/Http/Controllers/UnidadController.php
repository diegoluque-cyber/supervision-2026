<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Exports\UnidadesExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnidadController extends Controller
{
    public function index(Request $request): Response
    {
        $categoria = $request->string('categoria')->toString();
        $search = ltrim($request->string('search')->trim()->toString(), '#');

        $query = Unidad::query()->with('conductor');

        if ($categoria && array_key_exists($categoria, Unidad::CATEGORIAS)) {
            $query->whereHas('faltas', function ($q) use ($categoria) {
                $q->where('categoria', $categoria);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                // Búsqueda exacta por código de unidad
                $q->whereRaw('UPPER(codigo) = ?', [strtoupper($search)])
                    // O búsqueda parcial por placa o conductor
                    ->orWhere('placa', 'like', "%{$search}%")
                    ->orWhereHas('conductor', function ($cq) use ($search) {
                        $cq->where('nombre', 'like', "%{$search}%")
                            ->orWhere('dni', 'like', "%{$search}%");
                    });
            });
        }

        $stats = [
            'total' => Unidad::count(),
            'activo' => Unidad::where('estado', 'activo')->count(),
            'mantenimiento' => Unidad::where('estado', 'mantenimiento')->count(),
            'inactivo' => Unidad::where('estado', 'inactivo')->count(),
        ];

        // Filtrar categorías para excluir 'supervisor' y 'supervisor_general' de los filtros y contadores superiores
        $categoriasFiltradas = array_filter(
            Unidad::CATEGORIAS,
            fn($key) => $key !== 'supervisor' && $key !== 'supervisor_general',
            ARRAY_FILTER_USE_KEY
        );

        foreach (array_keys($categoriasFiltradas) as $cat) {
            $stats[$cat] = \App\Models\Falta::where('categoria', $cat)->count();
        }

        return Inertia::render('Unidades/Index', [
            'unidades' => $query->orderBy('codigo')->paginate(30)->withQueryString(),
            'filters' => [
                'categoria' => $categoria ?: null,
                'search' => $search ?: null,
            ],
            'stats' => $stats,
            'categorias' => Unidad::CATEGORIAS,
        ]);
    }

    public function destroyMultiple(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:unidades,id'],
        ]);

        Unidad::whereIn('id', $data['ids'])->get()->each(function ($unidad) {
            // Desvincular conductor si tiene uno
            if ($unidad->conductor_id) {
                $unidad->conductor()->dissociate();
                $unidad->save();
            }
            $unidad->delete();
        });

        $count = count($data['ids']);
        return back()->with('success', "{$count} unidad(es) eliminada(s).");
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'categoria' => $request->string('categoria')->toString(),
            'search' => ltrim($request->string('search')->trim()->toString(), '#'),
        ];

        $filename = 'padron_unidades_' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new UnidadesExport($filters), $filename);
    }
}
