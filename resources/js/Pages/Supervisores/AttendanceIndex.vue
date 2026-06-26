<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const page = usePage();
const successMsg = computed(() => page.props.flash?.success);

const props = defineProps({
    meetings: Object, // Paginado
    stats: Object, // KPIs globales
    statsSupervisores: Array, // Estadísticas por supervisor
});

// ── Crear Reunión Modal ──────────────────────────────────────
const showRegModal = ref(false);
const regForm = useForm({
    fecha: '',
    tema: '',
});

// Sugerir el miércoles más cercano (próximo o pasado)
const getUpcomingWednesday = () => {
    const d = new Date();
    const day = d.getDay();
    const diff = (day <= 3) ? (3 - day) : (10 - day); // miércoles es 3
    d.setDate(d.getDate() + diff);
    return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().split('T')[0];
};

const openRegModal = () => {
    regForm.reset();
    regForm.fecha = getUpcomingWednesday();
    regForm.tema = 'Reunión Ordinaria de Supervisión';
    showRegModal.value = true;
};

const submitMeeting = () => {
    regForm.post(route('supervisores.asistencia.store'), {
        onSuccess: () => {
            showRegModal.value = false;
        }
    });
};

const deleteMeeting = (id) => {
    if (confirm('¿Seguro que deseas eliminar esta reunión? Se borrará todo el registro de asistencia de este día.')) {
        router.delete(route('supervisores.asistencia.destroy', id), {
            preserveScroll: true,
        });
    }
};

const getPercentageColor = (pct) => {
    if (pct >= 80) return 'bg-emerald-500/10 text-emerald-600 dark:text-[#00ff66]';
    if (pct >= 50) return 'bg-orange-500/10 text-orange-600 dark:text-orange-400';
    return 'bg-red-500/10 text-red-600 dark:text-red-400';
};
</script>

<template>
    <Alo45Layout>
        <Head title="Asistencia de Supervisores — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Administración</p>
                    <h1 class="mt-1 text-2xl font-black text-white">Asistencia de Supervisores</h1>
                    <p class="mt-1 text-sm text-gray-300/80">
                        Control de asistencia y puntualidad a las reuniones de supervisores celebradas cada miércoles.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="openRegModal"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-emerald-500/25"
                    >
                        <span>+ Registrar Reunión</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Tabs de Selección -->
        <div class="mb-6 border-b border-gray-200 dark:border-neutral-800 flex gap-4">
            <Link
                :href="route('supervisores.index')"
                class="pb-3 text-sm font-black border-b-2 px-1 transition duration-200"
                :class="route().current('supervisores.index')
                    ? 'border-emerald-500 text-emerald-600 dark:text-[#00ff66]'
                    : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white'"
            >
                👤 Supervisores Registrados
            </Link>
            <Link
                :href="route('supervisores.asistencia.index')"
                class="pb-3 text-sm font-black border-b-2 px-1 transition duration-200"
                :class="route().current('supervisores.asistencia.*')
                    ? 'border-emerald-500 text-emerald-600 dark:text-[#00ff66]'
                    : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white'"
            >
                📅 Lista de Asistencia (Miércoles)
            </Link>
        </div>

        <div v-if="successMsg" class="mb-4 flex items-center justify-center gap-2.5 rounded-xl border border-alo-green bg-green-50 px-5 py-3.5 text-sm font-semibold text-alo-green shadow-sm">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ successMsg }}
        </div>

        <!-- KPI Glass Cards -->
        <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="kpi-glass kpi-glow-emerald kpi-border-emerald rounded-2xl p-5 relative overflow-hidden bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Total Reuniones</p>
                        <h3 class="stat-number mt-2 text-3.5xl font-black text-gray-900 dark:text-white">{{ stats.total_reuniones }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 text-xl dark:bg-emerald-500/20 dark:text-emerald-400 shadow-inner">
                        📅
                    </div>
                </div>
            </div>

            <div class="kpi-glass kpi-glow-green kpi-border-green rounded-2xl p-5 relative overflow-hidden bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Tasa Asistencia Global</p>
                        <h3 class="stat-number mt-2 text-3.5xl font-black text-gray-900 dark:text-white">{{ stats.tasa_asistencia }}%</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 text-xl dark:bg-emerald-500/20 dark:text-emerald-400 shadow-inner">
                        📈
                    </div>
                </div>
            </div>

            <div class="kpi-glass kpi-glow-orange kpi-border-orange rounded-2xl p-5 relative overflow-hidden bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Supervisoras (Móviles)</p>
                        <h3 class="stat-number mt-2 text-3.5xl font-black text-gray-900 dark:text-white">{{ stats.total_supervisores }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500 text-xl dark:bg-orange-500/20 dark:text-orange-400 shadow-inner">
                        🚖
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Columna Izquierda: Reuniones del Historial (2/3 de ancho) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white/80 dark:bg-neutral-900/90 rounded-2xl border border-gray-100 dark:border-neutral-800 p-5 shadow-sm">
                    <h2 class="text-base font-black text-gray-900 dark:text-white mb-4">Reuniones de los Miércoles</h2>

                    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-neutral-800/80">
                        <table class="w-full border-collapse text-left text-sm text-gray-700 dark:text-neutral-300">
                            <thead class="bg-gray-50/50 dark:bg-neutral-950/40 text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-400 border-b border-gray-100 dark:border-neutral-800">
                                <tr>
                                    <th class="px-5 py-3">Fecha</th>
                                    <th class="px-5 py-3">Tema / Notas</th>
                                    <th class="px-5 py-3 text-center">Asistieron</th>
                                    <th class="px-5 py-3 text-center">Tardanzas</th>
                                    <th class="px-5 py-3 text-center">Faltas</th>
                                    <th class="px-5 py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                                <tr v-if="meetings.data.length === 0" class="hover:bg-transparent">
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-400 dark:text-neutral-500 font-bold">
                                        No hay reuniones registradas.
                                    </td>
                                </tr>
                                <tr v-for="m in meetings.data" :key="m.id" class="hover:bg-emerald-500/5 transition">
                                    <td class="px-5 py-3 whitespace-nowrap font-mono text-xs font-bold text-gray-800 dark:text-neutral-100">
                                        {{ m.fecha_label }}
                                    </td>
                                    <td class="px-5 py-3 text-xs max-w-[200px] truncate font-semibold text-gray-900 dark:text-white">
                                        {{ m.tema }}
                                    </td>
                                    <td class="px-5 py-3 text-center font-bold text-emerald-600 dark:text-[#00ff66]">
                                        {{ m.asistio }}
                                    </td>
                                    <td class="px-5 py-3 text-center font-bold text-orange-500">
                                        {{ m.tardanza }}
                                    </td>
                                    <td class="px-5 py-3 text-center font-bold text-red-500">
                                        {{ m.falta + m.justificado }}
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('supervisores.asistencia.show', m.id)"
                                                class="rounded-lg bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-600 dark:text-[#00ff66] px-2.5 py-1 text-xs font-black transition"
                                            >
                                                Ver / Pases
                                            </Link>
                                            <button
                                                @click="deleteMeeting(m.id)"
                                                class="text-gray-400 hover:text-red-500 transition text-xs p-1"
                                                title="Eliminar Reunión"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="meetings.links.length > 3" class="mt-4 flex justify-center gap-1">
                        <template v-for="link in meetings.links" :key="link.label">
                            <div
                                v-if="link.url === null"
                                class="px-3 py-1.5 text-xs text-gray-400 dark:text-neutral-600 rounded-lg"
                                v-html="link.label"
                            ></div>
                            <Link
                                v-else
                                :href="link.url"
                                class="px-3 py-1.5 text-xs font-black rounded-lg transition"
                                :class="link.active ? 'bg-emerald-600 text-white' : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100'"
                                v-html="link.label"
                            ></Link>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Resumen de Asistencia por Supervisor (1/3 de ancho) -->
            <div class="lg:col-span-1">
                <div class="bg-white/80 dark:bg-neutral-900/90 rounded-2xl border border-gray-100 dark:border-neutral-800 p-5 shadow-sm">
                    <h2 class="text-base font-black text-gray-900 dark:text-white mb-1">Estadísticas por Supervisor</h2>
                    <p class="text-[10px] text-gray-400 mb-4 uppercase tracking-wider font-semibold">Tasa de puntualidad acumulada</p>

                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                        <div
                            v-for="s in statsSupervisores"
                            :key="s.codigo"
                            class="flex items-center justify-between p-3 rounded-xl border border-gray-100 dark:border-neutral-850 hover:bg-gray-50/50 dark:hover:bg-neutral-950/20 transition"
                        >
                            <div class="min-w-0 flex-1 pr-2">
                                <p class="text-xs font-black text-gray-900 dark:text-white truncate">
                                    {{ s.conductor }}
                                </p>
                                <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 font-mono">
                                    Móvil #{{ s.codigo }} · Placa: {{ s.placa }}
                                </p>
                                <p class="text-[9px] text-gray-500 dark:text-neutral-500 mt-1 font-mono">
                                    Asistió: {{ s.asistio }} | Tardes: {{ s.tardanza }} | Faltas: {{ s.falta }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block rounded-full px-2.5 py-1 text-xs font-black font-mono shadow-sm" :class="getPercentageColor(s.porcentaje)">
                                    {{ s.porcentaje }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: REGISTRAR REUNIÓN -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showRegModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-950/60 backdrop-blur-sm" @click="showRegModal = false"></div>

                <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-850 p-6 shadow-2xl z-10 text-gray-900 dark:text-white">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800 pb-3">
                        <h2 class="text-base font-black text-gray-900 dark:text-white">📅 Programar Nueva Reunión</h2>
                        <button @click="showRegModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white font-black text-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitMeeting" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Fecha de la Reunión (Miércoles) *</label>
                            <input
                                v-model="regForm.fecha"
                                type="date"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Tema / Descripción de Reunión</label>
                            <input
                                v-model="regForm.tema"
                                type="text"
                                placeholder="Ej: Reunión de Coordinación y Planificación"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-neutral-800 pt-4 mt-6">
                            <button
                                type="button"
                                @click="showRegModal = false"
                                class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-neutral-800 dark:text-neutral-400 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="regForm.processing"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg transition disabled:opacity-50"
                            >
                                Crear Reunión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Alo45Layout>
</template>
