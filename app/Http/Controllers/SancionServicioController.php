<?php

namespace App\Http\Controllers;

use App\Models\Conductor;
use App\Models\SancionServicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SancionServicioController extends Controller
{
    public function index(Request $request): Response
    {
        $activas = SancionServicio::with('conductor.unidad')
            ->where('estado', 'pendiente')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => $this->formatSancion($s));

        $historial = SancionServicio::with('conductor.unidad')
            ->whereIn('estado', ['completado', 'exonerado'])
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->map(fn($s) => $this->formatSancion($s));

        $conductores = Conductor::with('unidad')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'dni' => $c->dni,
                'unidad_codigo' => $c->unidad->codigo ?? null,
                'unidad_placa' => $c->unidad->placa ?? null,
                'categoria_label' => $c->unidad ? $c->unidad->categoria_label : 'Conductor',
            ]);

        $completadosHoy = SancionServicio::whereIn('estado', ['completado', 'exonerado'])
            ->whereDate('updated_at', today())
            ->count();

        $stats = [
            'sancionados_activos' => SancionServicio::where('estado', 'pendiente')->count(),
            'completados_hoy'     => $completadosHoy,
        ];

        return Inertia::render('Sanciones/Index', [
            'activas'     => $activas,
            'historial'   => $historial,
            'conductores' => $conductores,
            'stats'       => $stats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'conductor_id'          => ['required', 'exists:conductores,id'],
            'negociable_count'      => ['required', 'integer', 'min:0', 'max:5'],
            'corporativo_count'     => ['required', 'integer', 'min:0', 'max:5'],
            'categoria_corporativo' => ['nullable', 'required_if:corporativo_count,>0', 'string', 'in:san_pablo,mitsui,cerro_verde'],
            'motivo'                => ['required', 'string', 'max:1000'],
        ]);

        $negociable_status = array_fill(0, $data['negociable_count'], 'pending');
        $corporativo_status = array_fill(0, $data['corporativo_count'], 'pending');

        SancionServicio::create([
            'conductor_id'          => $data['conductor_id'],
            'categoria_corporativo' => $data['categoria_corporativo'] ?? null,
            'negociable_status'     => $negociable_status,
            'corporativo_status'    => $corporativo_status,
            'motivo'                => $data['motivo'],
            'estado'                => 'pendiente',
        ]);

        return back()->with('success', 'Sanción de servicios registrada correctamente.');
    }

    public function updateStatus(Request $request, SancionServicio $sancion): RedirectResponse
    {
        $data = $request->validate([
            'type'   => ['required', 'in:negociable,corporativo'],
            'index'  => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:pending,ok,fail'],
        ]);

        $field = $data['type'] . '_status';
        $statusArray = $sancion->$field;

        if (isset($statusArray[$data['index']])) {
            $statusArray[$data['index']] = $data['status'];
            $sancion->update([
                $field => $statusArray,
            ]);
        }

        return back()->with('success', 'Estado de servicio actualizado.');
    }

    public function liberar(Request $request, SancionServicio $sancion): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:completar,exonerar'],
        ]);

        $sancion->update([
            'estado' => $data['action'] === 'completar' ? 'completado' : 'exonerado',
        ]);

        return back()->with('success', 'Conductor liberado de la sanción correctamente.');
    }

    public function addService(Request $request, SancionServicio $sancion): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:negociable,corporativo'],
        ]);

        $field = $data['type'] . '_status';
        $statusArray = $sancion->$field ?? [];
        $statusArray[] = 'pending';

        $sancion->update([
            $field => $statusArray,
        ]);

        return back()->with('success', 'Servicio agregado a la sanción.');
    }

    public function removeService(Request $request, SancionServicio $sancion): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:negociable,corporativo'],
        ]);

        $field = $data['type'] . '_status';
        $statusArray = $sancion->$field ?? [];
        
        if (count($statusArray) > 0) {
            array_pop($statusArray);
            $sancion->update([
                $field => $statusArray,
            ]);
        }

        return back()->with('success', 'Servicio removido de la sanción.');
    }

    public function clearHistory(): RedirectResponse
    {
        SancionServicio::whereIn('estado', ['completado', 'exonerado'])->delete();

        return back()->with('success', 'El historial de liberados ha sido limpiado.');
    }

    private function formatSancion(SancionServicio $s): array
    {
        return [
            'id'                     => $s->id,
            'conductor_id'           => $s->conductor_id,
            'conductor_nombre'       => $s->conductor->nombre ?? '—',
            'conductor_dni'          => $s->conductor->dni ?? '—',
            'conductor_telefono'     => $s->conductor->telefono ?? '—',
            'unidad_codigo'          => $s->conductor->unidad->codigo ?? null,
            'unidad_placa'           => $s->conductor->unidad->placa ?? null,
            'conductor_categoria'    => $s->conductor->unidad ? $s->conductor->unidad->categoria_label : 'Conductor',
            'categoria_corporativo'  => $s->categoria_corporativo,
            'negociable_status'      => $s->negociable_status,
            'corporativo_status'     => $s->corporativo_status,
            'motivo'                 => $s->motivo,
            'estado'                 => $s->estado,
            'created_at'             => $s->created_at ? $s->created_at->format('d/m/Y H:i') : '—',
            'updated_at'             => $s->updated_at ? $s->updated_at->format('d/m/Y H:i') : '—',
        ];
    }
}
