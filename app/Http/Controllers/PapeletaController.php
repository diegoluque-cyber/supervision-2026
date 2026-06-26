<?php

namespace App\Http\Controllers;

use App\Models\Papeleta;
use App\Models\Unidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PapeletaController extends Controller
{
    public function store(Request $request, Unidad $unidad): RedirectResponse
    {
        $data = $request->validate([
            'nro_papeleta'  => ['required', 'string', 'max:50'],
            'monto'         => ['required', 'numeric', 'min:0'],
            'fecha_emision' => ['required', 'date'],
            'infraccion'    => ['nullable', 'string', 'max:255'],
            'estado_pago'   => ['required', 'in:pendiente,pagado'],
        ]);

        Papeleta::create([
            'unidad_id'    => $unidad->id,
            'conductor_id' => $unidad->conductor_id,
            'nro_papeleta' => $data['nro_papeleta'],
            'monto'        => $data['monto'],
            'fecha_emision'=> $data['fecha_emision'],
            'infraccion'   => $data['infraccion'],
            'estado_pago'  => $data['estado_pago'],
            'fecha_pago'   => $data['estado_pago'] === 'pagado' ? now() : null,
        ]);

        return back()->with('success', 'Papeleta registrada correctamente.');
    }

    public function togglePago(Papeleta $papeleta): RedirectResponse
    {
        $nuevoEstado = $papeleta->estado_pago === 'pagado' ? 'pendiente' : 'pagado';
        
        $papeleta->update([
            'estado_pago' => $nuevoEstado,
            'fecha_pago' => $nuevoEstado === 'pagado' ? now() : null,
        ]);

        if ($nuevoEstado === 'pendiente') {
            return back()->with('warning', 'Pago de la papeleta revertido a pendiente.');
        }

        return back()->with('success', 'Pago de la papeleta registrado correctamente.');
    }

    public function destroy(Papeleta $papeleta): RedirectResponse
    {
        $papeleta->delete();
        return back()->with('success', 'Papeleta eliminada correctamente.');
    }

    public function caja(Request $request): Response
    {
        $search = ltrim($request->string('search')->trim()->toString(), '#');
        $estado = $request->string('estado')->toString();

        $query = Papeleta::with(['unidad', 'conductor']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nro_papeleta', 'like', "%{$search}%")
                  ->orWhere('infraccion', 'like', "%{$search}%")
                  ->orWhereHas('unidad', function ($uq) use ($search) {
                      $uq->where('codigo', $search)
                         ->orWhere('placa', 'like', "%{$search}%");
                  })
                  ->orWhereHas('conductor', function ($cq) use ($search) {
                      $cq->where('nombre', 'like', "%{$search}%")
                         ->orWhere('dni', 'like', "%{$search}%");
                  });
            });
        }

        if (in_array($estado, ['pendiente', 'pagado'], true)) {
            $query->where('estado_pago', $estado);
        }

        $papeletas = $query->orderByRaw("CASE WHEN estado_pago = 'pendiente' THEN 0 ELSE 1 END")
            ->orderByDesc('fecha_emision')
            ->paginate(30)
            ->withQueryString();

        $hoy = now()->toDateString();
        $stats = [
            'total_pendiente' => (float) Papeleta::where('estado_pago', 'pendiente')->sum('monto'),
            'total_pagado_hoy' => (float) Papeleta::where('estado_pago', 'pagado')
                ->whereDate('fecha_pago', $hoy)
                ->sum('monto'),
            'cant_pendiente' => Papeleta::where('estado_pago', 'pendiente')->count(),
            'cant_pagado_hoy' => Papeleta::where('estado_pago', 'pagado')
                ->whereDate('fecha_pago', $hoy)
                ->count(),
        ];

        // Unidades con conductor para el formulario de caja
        $unidades = Unidad::with('conductor')->orderBy('codigo')->get()->map(fn ($u) => [
            'id' => $u->id,
            'codigo' => $u->codigo,
            'placa' => $u->placa,
            'conductor_nombre' => $u->conductor?->nombre ?? 'Sin conductor',
        ]);

        return Inertia::render('Caja/Index', [
            'papeletas' => $papeletas,
            'filters' => [
                'search' => $search ?: null,
                'estado' => $estado ?: null,
            ],
            'stats' => $stats,
            'unidades' => $unidades,
        ]);
    }

    public function storeGeneral(Request $request): RedirectResponse
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
        ]);

        return back()->with('success', 'Papeleta registrada correctamente.');
    }
}
