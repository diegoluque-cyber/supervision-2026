<?php

namespace App\Http\Controllers;

use App\Models\SupervisorMeeting;
use App\Models\SupervisorAttendance;
use App\Models\Unidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupervisorAttendanceController extends Controller
{
    /**
     * Listado de reuniones y estadísticas de asistencia.
     */
    public function index(Request $request): Response
    {
        // 1. Obtener listado de reuniones paginadas
        $meetings = SupervisorMeeting::withCount([
            'attendances as asistio_count' => fn($q) => $q->where('estado', 'asistio'),
            'attendances as tardanza_count' => fn($q) => $q->where('estado', 'tardanza'),
            'attendances as falta_count' => fn($q) => $q->where('estado', 'falta'),
            'attendances as justificado_count' => fn($q) => $q->where('estado', 'justificado'),
        ])->orderByDesc('fecha')->paginate(15);

        // Mapear reuniones para visualización
        $reuniones = $meetings->map(fn($m) => [
            'id' => $m->id,
            'fecha' => $m->fecha->format('Y-m-d'),
            'fecha_label' => $m->fecha->format('d/m/Y'),
            'tema' => $m->tema ?? 'Reunión Ordinaria',
            'asistio' => $m->asistio_count,
            'tardanza' => $m->tardanza_count,
            'falta' => $m->falta_count,
            'justificado' => $m->justificado_count,
        ]);

        // 2. Obtener lista de unidades de supervisores
        $supervisoresUnidades = Unidad::where('categoria', 'supervisor')
            ->with('conductor')
            ->orderByRaw("CAST(codigo AS UNSIGNED) ASC, codigo ASC")
            ->get();

        // 3. Calcular estadísticas acumuladas por supervisor/unidad
        $totalMeetingsCount = SupervisorMeeting::count();
        
        $statsSupervisores = $supervisoresUnidades->map(function($u) use ($totalMeetingsCount) {
            $attendances = SupervisorAttendance::where('unidad_id', $u->id)->get();
            
            $asistio = $attendances->where('estado', 'asistio')->count();
            $tardanza = $attendances->where('estado', 'tardanza')->count();
            $falta = $attendances->where('estado', 'falta')->count();
            $justificado = $attendances->where('estado', 'justificado')->count();

            // Calcular tasa de asistencia: (asistio + tardanza + justificado) / total
            $asistenciasCalculadas = $asistio + $tardanza + $justificado;
            $ratio = $totalMeetingsCount > 0 ? ($asistenciasCalculadas / $totalMeetingsCount) * 100 : 100;

            return [
                'unidad_id' => $u->id,
                'codigo' => $u->codigo,
                'placa' => $u->placa,
                'conductor' => $u->conductor?->nombre ?? 'Sin conductor',
                'asistio' => $asistio,
                'tardanza' => $tardanza,
                'falta' => $falta,
                'justificado' => $justificado,
                'porcentaje' => round($ratio, 1),
            ];
        });

        // 4. Calcular KPIs globales
        $totalAsistencias = SupervisorAttendance::count();
        $totalPresentes = SupervisorAttendance::whereIn('estado', ['asistio', 'tardanza'])->count();
        $globalAttendanceRate = $totalAsistencias > 0 ? ($totalPresentes / $totalAsistencias) * 100 : 100;

        $statsGlobales = [
            'total_reuniones' => $totalMeetingsCount,
            'tasa_asistencia' => round($globalAttendanceRate, 1),
            'total_supervisores' => $supervisoresUnidades->count(),
            'proxima_fecha' => now()->next('Wednesday')->format('d/m/Y'),
        ];

        return Inertia::render('Supervisores/AttendanceIndex', [
            'meetings' => [
                'data' => $reuniones,
                'links' => $meetings->linkCollection()->toArray(),
            ],
            'stats' => $statsGlobales,
            'statsSupervisores' => $statsSupervisores,
        ]);
    }

    /**
     * Crear una nueva reunión y pre-inicializar las asistencias en "asistio" para todos los supervisores.
     */
    public function storeMeeting(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fecha' => ['required', 'date'],
            'tema' => ['nullable', 'string', 'max:255'],
        ]);

        // Evitar duplicar reuniones en la misma fecha
        $exists = SupervisorMeeting::whereDate('fecha', $data['fecha'])->exists();
        if ($exists) {
            return back()->withErrors(['fecha' => 'Ya existe una reunión registrada para esta fecha.']);
        }

        $meeting = SupervisorMeeting::create($data);

        // Pre-poblar asistencia con estado 'asistio'
        $supervisores = Unidad::where('categoria', 'supervisor')->get();
        foreach ($supervisores as $u) {
            SupervisorAttendance::create([
                'meeting_id' => $meeting->id,
                'unidad_id' => $u->id,
                'estado' => 'asistio',
                'observacion' => null,
            ]);
        }

        return redirect()->route('supervisores.asistencia.show', $meeting->id)
                         ->with('success', 'Reunión creada. Pasa lista a continuación.');
    }

    /**
     * Ver hoja de asistencia de una reunión específica.
     */
    public function showMeeting(SupervisorMeeting $meeting): Response
    {
        $meetingData = [
            'id' => $meeting->id,
            'fecha' => $meeting->fecha->format('Y-m-d'),
            'fecha_label' => $meeting->fecha->format('d/m/Y'),
            'tema' => $meeting->tema ?? 'Reunión Ordinaria',
        ];

        // Obtener todas las asistencias vinculadas a esta reunión
        $attendances = SupervisorAttendance::where('meeting_id', $meeting->id)
            ->with(['unidad.conductor'])
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'unidad_id' => $a->unidad_id,
                'codigo' => $a->unidad->codigo ?? '—',
                'placa' => $a->unidad->placa ?? '—',
                'conductor' => $a->unidad->conductor->nombre ?? 'Sin conductor',
                'estado' => $a->estado,
                'observacion' => $a->observacion,
            ])
            ->sortBy('codigo')
            ->values();

        return Inertia::render('Supervisores/AttendanceShow', [
            'meeting' => $meetingData,
            'attendances' => $attendances,
        ]);
    }

    /**
     * Guardar la asistencia de la reunión.
     */
    public function saveAttendance(Request $request, SupervisorMeeting $meeting): RedirectResponse
    {
        $data = $request->validate([
            'asistencias' => ['required', 'array'],
            'asistencias.*.unidad_id' => ['required', 'exists:unidades,id'],
            'asistencias.*.estado' => ['required', 'in:asistio,tardanza,falta,justificado'],
            'asistencias.*.observacion' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['asistencias'] as $item) {
            SupervisorAttendance::updateOrCreate(
                [
                    'meeting_id' => $meeting->id,
                    'unidad_id' => $item['unidad_id'],
                ],
                [
                    'estado' => $item['estado'],
                    'observacion' => $item['observacion'] ?? null,
                ]
            );
        }

        return redirect()->route('supervisores.asistencia.index')
                         ->with('success', 'Asistencia guardada correctamente.');
    }

    /**
     * Eliminar una reunión.
     */
    public function destroyMeeting(SupervisorMeeting $meeting): RedirectResponse
    {
        $meeting->delete();

        return redirect()->route('supervisores.asistencia.index')
                         ->with('success', 'Reunión de asistencia eliminada.');
    }
}
