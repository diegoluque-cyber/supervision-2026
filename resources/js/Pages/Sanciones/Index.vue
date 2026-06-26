<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const props = defineProps({
    activas:     Array,
    historial:   Array,
    conductores: Array,
    stats:       Object,
});

// Pestaña activa (Activas vs Historial)
const activeTab = ref('activas');

// Formulario de Sanción
const showRegModal = ref(false);
const conductorSearchQuery = ref('');
const showConductorDropdown = ref(false);

const form = useForm({
    conductor_id:          '',
    negociable_count:      3,
    corporativo_count:     0,
    categoria_corporativo: '',
    motivo:                '',
});

// Filtrar conductores para el autocompletado
const filteredConductores = computed(() => {
    const q = conductorSearchQuery.value.trim().toUpperCase();
    if (!q) return props.conductores.slice(0, 10);
    return props.conductores.filter(c => 
        c.nombre.toUpperCase().includes(q) || 
        c.dni.toUpperCase().includes(q) ||
        (c.unidad_codigo && c.unidad_codigo.toUpperCase().includes(q))
    ).slice(0, 10);
});

const selectConductor = (c) => {
    form.conductor_id = c.id;
    conductorSearchQuery.value = `${c.nombre} [${c.categoria_label || 'Conductor'}] (DNI: ${c.dni})` + (c.unidad_codigo ? ` - Unidad #${c.unidad_codigo}` : '');
    showConductorDropdown.value = false;
};

const handleConductorBlur = () => {
    setTimeout(() => {
        showConductorDropdown.value = false;
    }, 250);
};

const openRegModal = () => {
    form.reset();
    conductorSearchQuery.value = '';
    showRegModal.value = true;
};

const submitSancion = () => {
    form.post(route('sanciones.store'), {
        onSuccess: () => {
            showRegModal.value = false;
            form.reset();
        }
    });
};

// ── Rotación interactiva de estado del servicio ─────────────────
const cycleStatus = (sancionId, type, index, currentStatus) => {
    const nextStatusMap = {
        'pending': 'ok',
        'ok':      'fail',
        'fail':    'pending'
    };
    const nextStatus = nextStatusMap[currentStatus] || 'pending';

    router.put(route('sanciones.update-status', sancionId), {
        type:   type,
        index:  index,
        status: nextStatus
    }, {
        preserveScroll: true
    });
};

const addServiceSlot = (sancionId, type) => {
    router.put(route('sanciones.add-service', sancionId), {
        type: type
    }, {
        preserveScroll: true
    });
};

const removeServiceSlot = (sancionId, type) => {
    router.put(route('sanciones.remove-service', sancionId), {
        type: type
    }, {
        preserveScroll: true
    });
};

// ── Procesar Liberación ─────────────────────────────────────────
const procesarLiberacion = (sancionId, action) => {
    const textMap = {
        'completar': '¿Seguro que deseas completar la sanción y liberar a este conductor?',
        'exonerar':  '¿Seguro que deseas exonerar de la sanción a este conductor?'
    };
    if (confirm(textMap[action])) {
        router.put(route('sanciones.liberar', sancionId), {
            action: action
        }, {
            preserveScroll: true
        });
    }
};

const clearHistory = () => {
    if (confirm('¿Seguro que deseas limpiar todo el historial de liberados? Esta acción no se puede deshacer.')) {
        router.post(route('sanciones.clear-history'), {}, {
            preserveScroll: true
        });
    }
};

// Determina si una sanción activa tiene todos sus servicios completados
const isListoParaLiberar = (sancion) => {
    const negociableList = sancion.negociable_status || [];
    const corporativoList = sancion.corporativo_status || [];

    const todosNegociableOk = negociableList.length === 0 || negociableList.every(s => s === 'ok');
    const todosCorporativoOk = corporativoList.length === 0 || corporativoList.every(s => s === 'ok');

    return todosNegociableOk && todosCorporativoOk;
};

const getCorporativoLabel = (key) => {
    return {
        'san_pablo':   'San Pablo',
        'mitsui':      'Mitsui',
        'cerro_verde': 'Cerro Verde'
    }[key] || key;
};
</script>

<template>
    <Alo45Layout>
        <Head title="Sanciones de Servicios — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Gestión de Cumplimiento</p>
                    <h1 class="mt-1 text-2xl font-black text-white">Sanciones de Servicios Diarios</h1>
                    <p class="mt-1 text-sm text-gray-300/80">
                        Asigna y controla de forma interactiva las cuotas de servicios obligatorios para la liberación de conductores.
                    </p>
                </div>

                <div>
                    <button
                        @click="openRegModal"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-red-500/25"
                    >
                        <span>+ Registrar Sanción</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Stats Overview -->
        <div class="mb-8 grid gap-4 sm:grid-cols-2">
            <div class="relative overflow-hidden rounded-2xl border border-red-500/10 bg-red-50/70 dark:bg-red-500/5 dark:border-red-500/10 p-5 backdrop-blur-xl shadow-sm">
                <div class="absolute -right-4 -top-4 text-red-500/10 text-7xl font-bold select-none pointer-events-none">⚖️</div>
                <p class="text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-400">Conductores Sancionados</p>
                <h3 class="mt-2 text-3xl font-black text-red-700 dark:text-white font-mono">{{ stats.sancionados_activos }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">Actualmente bajo sanción obligatoria</p>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-emerald-500/10 bg-emerald-50/70 dark:bg-emerald-500/5 dark:border-emerald-500/10 p-5 backdrop-blur-xl shadow-sm">
                <div class="absolute -right-4 -top-4 text-emerald-500/10 text-7xl font-bold select-none pointer-events-none">✓</div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Liberados Hoy</p>
                <h3 class="mt-2 text-3xl font-black text-emerald-700 dark:text-white font-mono">{{ stats.completados_hoy }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">Cumplieron sus cuotas y fueron liberados</p>
            </div>


        </div>

        <!-- Tabs Activas vs Historial -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 dark:border-gray-800 pb-px gap-3">
            <div class="flex gap-2">
                <button
                    @click="activeTab = 'activas'"
                    class="border-b-2 px-5 py-3 text-sm font-bold transition-all"
                    :class="activeTab === 'activas'
                        ? 'border-red-600 text-red-600 dark:text-red-400'
                        : 'border-transparent text-gray-500 hover:text-gray-800 dark:hover:text-white'"
                >
                    🔥 Sanciones Activas ({{ activas.length }})
                </button>
                <button
                    @click="activeTab = 'historial'"
                    class="border-b-2 px-5 py-3 text-sm font-bold transition-all"
                    :class="activeTab === 'historial'
                        ? 'border-red-600 text-red-600 dark:text-red-400'
                        : 'border-transparent text-gray-500 hover:text-gray-800 dark:hover:text-white'"
                >
                    📁 Historial de Liberados
                </button>
            </div>

            <div v-if="activeTab === 'historial' && historial.length > 0" class="pb-2 sm:pb-0">
                <button
                    @click="clearHistory"
                    class="flex items-center gap-1.5 rounded-lg bg-gray-100 hover:bg-red-50 hover:text-red-600 dark:bg-gray-800 dark:hover:bg-red-950/40 dark:hover:text-red-400 px-3.5 py-1.5 text-xs font-bold text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700 transition-all duration-200"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Limpiar Historial
                </button>
            </div>
        </div>

        <!-- LISTA DE SANCIONES ACTIVAS -->
        <div v-if="activeTab === 'activas'">
            <div v-if="activas.length === 0" class="py-12 text-center text-sm text-gray-400 dark:text-gray-300 italic">
                Ningún conductor tiene sanciones de servicios activas en este momento.
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2">
                <div
                    v-for="sancion in activas"
                    :key="sancion.id"
                    class="relative overflow-hidden rounded-2xl border bg-white p-5 shadow-sm dark:bg-gray-900/60 transition-all duration-300 hover:shadow-md"
                    :class="isListoParaLiberar(sancion) 
                        ? 'border-emerald-500/40 shadow-emerald-500/5 ring-1 ring-emerald-500/20' 
                        : 'border-gray-200 dark:border-gray-800'"
                >
                    <!-- Cabecera de la tarjeta con Flexbox para evitar superposición -->
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded bg-red-500/10 px-2 py-0.5 text-[10px] font-bold text-red-600 dark:text-red-400">Sancionado</span>
                                <span 
                                    class="rounded px-2 py-0.5 text-[10px] font-bold border"
                                    :class="(sancion.conductor_categoria || 'Conductor').includes('Supervisor') || (sancion.conductor_categoria || 'Conductor').includes('Supervisión')
                                        ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/25'
                                        : 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/25'"
                                >
                                    {{ sancion.conductor_categoria || 'Conductor' }}
                                </span>
                            </div>
                            <h3 class="mt-2 text-base font-black text-gray-800 dark:text-white">{{ sancion.conductor_nombre }}</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-300">
                                DNI: {{ sancion.conductor_dni }} 
                                <span v-if="sancion.unidad_codigo" class="ml-1.5 font-bold text-emerald-600 dark:text-emerald-400">
                                    Unidad #{{ sancion.unidad_codigo }}
                                </span>
                            </p>
                        </div>
                        <div v-if="isListoParaLiberar(sancion)" class="shrink-0 flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-black text-emerald-600 dark:text-emerald-400 border border-emerald-500/25">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            COMPLETADO
                        </div>
                    </div>

                    <!-- Datos adicionales del conductor -->
                    <div>
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300 italic">
                            <span class="font-bold text-gray-400">Motivo:</span> "{{ sancion.motivo }}"
                        </p>
                        <p class="mt-1 text-[10px] text-gray-400">Fecha de Registro: {{ sancion.created_at }}</p>
                    </div>

                    <hr class="my-4 border-gray-100 dark:border-gray-800" />

                    <!-- Checklist de Servicios -->
                    <div class="space-y-4">
                        <!-- Servicios Tarifa Negociable -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-[10px] font-black uppercase tracking-wide text-gray-400 dark:text-gray-300">
                                    💰 Servicios Tarifa Negociable ({{ sancion.negociable_status ? sancion.negociable_status.length : 0 }})
                                </p>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        @click="removeServiceSlot(sancion.id, 'negociable')"
                                        title="Quitar servicio/día"
                                        class="flex h-5 w-5 items-center justify-center rounded bg-gray-100 hover:bg-gray-200 text-[10px] font-bold text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition"
                                        :disabled="!sancion.negociable_status || sancion.negociable_status.length === 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': !sancion.negociable_status || sancion.negociable_status.length === 0 }"
                                    >
                                        -
                                    </button>
                                    <button
                                        @click="addServiceSlot(sancion.id, 'negociable')"
                                        title="Agregar servicio/día"
                                        class="flex h-5 w-5 items-center justify-center rounded bg-gray-100 hover:bg-gray-200 text-[10px] font-bold text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                            <div v-if="sancion.negociable_status && sancion.negociable_status.length" class="flex flex-wrap gap-2">
                                <button
                                    v-for="(status, index) in sancion.negociable_status"
                                    :key="index"
                                    @click="cycleStatus(sancion.id, 'negociable', index, status)"
                                    class="h-8 w-8 rounded-full border flex items-center justify-center text-xs font-black shadow-sm transition hover:scale-110 active:scale-95"
                                    :class="{
                                        'pending': 'bg-gray-100 border-gray-200 text-gray-400 hover:border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500',
                                        'ok':      'bg-emerald-500/10 border-emerald-500/40 text-emerald-600 dark:text-[#00ff66] dark:border-[#00ff66]/30',
                                        'fail':    'bg-red-500/10 border-red-500/40 text-red-600 dark:text-red-400 dark:border-red-500/30'
                                    }[status]"
                                    :title="`Servicio ${index + 1}: ${status === 'ok' ? 'Completado' : (status === 'fail' ? 'Fallido' : 'Pendiente')}. Haz clic para cambiar.`"
                                >
                                    <span v-if="status === 'ok'">✓</span>
                                    <span v-else-if="status === 'fail'">✕</span>
                                    <span v-else>{{ index + 1 }}</span>
                                </button>
                            </div>
                            <p v-else class="text-xs text-gray-450 dark:text-gray-400 italic">Sin servicios asignados.</p>
                        </div>

                        <!-- Servicios Corporativos (sólo si tiene categoría corporativa asignada) -->
                        <div v-if="sancion.categoria_corporativo">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-[10px] font-black uppercase tracking-wide text-gray-400 dark:text-gray-300">
                                    🏢 Serv. Corporativos - {{ getCorporativoLabel(sancion.categoria_corporativo) }} ({{ sancion.corporativo_status ? sancion.corporativo_status.length : 0 }})
                                </p>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        @click="removeServiceSlot(sancion.id, 'corporativo')"
                                        title="Quitar servicio corporativo/día"
                                        class="flex h-5 w-5 items-center justify-center rounded bg-gray-100 hover:bg-gray-200 text-[10px] font-bold text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition"
                                        :disabled="!sancion.corporativo_status || sancion.corporativo_status.length === 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': !sancion.corporativo_status || sancion.corporativo_status.length === 0 }"
                                    >
                                        -
                                    </button>
                                    <button
                                        @click="addServiceSlot(sancion.id, 'corporativo')"
                                        title="Agregar servicio corporativo/día"
                                        class="flex h-5 w-5 items-center justify-center rounded bg-gray-100 hover:bg-gray-200 text-[10px] font-bold text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                            <div v-if="sancion.corporativo_status && sancion.corporativo_status.length" class="flex flex-wrap gap-2">
                                <button
                                    v-for="(status, index) in sancion.corporativo_status"
                                    :key="index"
                                    @click="cycleStatus(sancion.id, 'corporativo', index, status)"
                                    class="h-8 w-8 rounded-full border flex items-center justify-center text-xs font-black shadow-sm transition hover:scale-110 active:scale-95"
                                    :class="{
                                        'pending': 'bg-gray-100 border-gray-200 text-gray-400 hover:border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500',
                                        'ok':      'bg-emerald-500/10 border-emerald-500/40 text-emerald-600 dark:text-[#00ff66] dark:border-[#00ff66]/30',
                                        'fail':    'bg-red-500/10 border-red-500/40 text-red-600 dark:text-red-400 dark:border-red-500/30'
                                    }[status]"
                                    :title="`Servicio ${index + 1}: ${status === 'ok' ? 'Completado' : (status === 'fail' ? 'Fallido' : 'Pendiente')}. Haz clic para cambiar.`"
                                >
                                    <span v-if="status === 'ok'">✓</span>
                                    <span v-else-if="status === 'fail'">✕</span>
                                    <span v-else>{{ index + 1 }}</span>
                                </button>
                            </div>
                            <p v-else class="text-xs text-gray-450 dark:text-gray-400 italic">Sin servicios asignados.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button
                            @click="procesarLiberacion(sancion.id, 'completar')"
                            class="flex-1 rounded-xl py-2 text-xs font-bold text-white transition-all"
                            :class="isListoParaLiberar(sancion)
                                ? 'bg-gradient-to-r from-emerald-600 to-teal-600 shadow-md shadow-emerald-500/20 hover:scale-102'
                                : 'bg-gray-300 dark:bg-gray-800 text-gray-500 cursor-not-allowed'"
                            :disabled="!isListoParaLiberar(sancion)"
                        >
                            ✓ Liberar Conductor
                        </button>
                        <button
                            @click="procesarLiberacion(sancion.id, 'exonerar')"
                            class="rounded-xl border border-gray-200 hover:border-red-500/40 hover:text-red-600 dark:border-gray-800 dark:hover:border-red-900/50 px-3 py-2 text-xs font-semibold text-gray-500 transition-all"
                            title="Exonerar de la sanción manualmente"
                        >
                            Exonerar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORIAL DE LIBERADOS -->
        <div v-if="activeTab === 'historial'">
            <div v-if="historial.length === 0" class="py-12 text-center text-sm text-gray-400 dark:text-gray-300 italic">
                No hay registros en el historial de sanciones liberadas.
            </div>

            <div v-else class="overflow-x-auto rounded-xl border border-gray-100 bg-white shadow-sm dark:bg-gray-900/50 dark:border-gray-800">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-bold uppercase tracking-wider text-gray-500 border-b dark:border-gray-800">
                            <th class="px-5 py-4">Conductor</th>
                            <th class="px-5 py-4">Unidad</th>
                            <th class="px-5 py-4">Motivo Sanción</th>
                            <th class="px-5 py-4">Servicios</th>
                            <th class="px-5 py-4">Resolución</th>
                            <th class="px-5 py-4">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800 text-sm text-gray-700 dark:text-gray-300">
                        <tr v-for="h in historial" :key="h.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/10 transition">
                            <td class="px-5 py-4">
                                <div class="font-bold text-gray-800 dark:text-white">{{ h.conductor_nombre }}</div>
                                <div class="text-xs text-gray-400">DNI: {{ h.conductor_dni }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span v-if="h.unidad_codigo" class="font-bold text-emerald-600 dark:text-emerald-400">
                                    #{{ h.unidad_codigo }}
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-5 py-4 max-w-xs truncate" :title="h.motivo">{{ h.motivo }}</td>
                            <td class="px-5 py-4">
                                <div class="space-y-1 text-xs">
                                    <div v-if="h.negociable_status && h.negociable_status.length" class="flex items-center gap-1">
                                        <span>💰 Negociable:</span>
                                        <div class="flex gap-0.5">
                                            <span 
                                                v-for="(st, sIdx) in h.negociable_status" 
                                                :key="sIdx"
                                                class="text-[9px] px-1 rounded font-bold"
                                                :class="st === 'ok' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600'"
                                            >
                                                {{ st === 'ok' ? '✓' : '✕' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="h.corporativo_status && h.corporativo_status.length" class="flex items-center gap-1">
                                        <span>🏢 Corp ({{ getCorporativoLabel(h.categoria_corporativo) }}):</span>
                                        <div class="flex gap-0.5">
                                            <span 
                                                v-for="(st, sIdx) in h.corporativo_status" 
                                                :key="sIdx"
                                                class="text-[9px] px-1 rounded font-bold"
                                                :class="st === 'ok' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600'"
                                            >
                                                {{ st === 'ok' ? '✓' : '✕' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span 
                                    class="rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="h.estado === 'completado'
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-[#00ff66]'
                                        : 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400'"
                                >
                                    {{ h.estado === 'completado' ? 'Cumplido' : 'Exonerado' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-400">{{ h.updated_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL: REGISTRAR SANCIÓN -->
        <Teleport to="body">
            <div v-if="showRegModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-md rounded-xl bg-white dark:bg-gray-900 shadow-xl border dark:border-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h2 class="font-bold text-alo-black dark:text-white">Registrar Sanción de Servicios</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-300">Asigna tareas de cumplimiento de servicios obligatorios</p>
                        </div>
                        <button @click="showRegModal = false" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-white">✕</button>
                    </div>

                    <form @submit.prevent="submitSancion" class="space-y-4 px-6 py-5">
                        <!-- Buscador de Conductor / Supervisor -->
                        <div class="relative">
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Conductor o Supervisor Sancionado *</label>
                            <input
                                v-model="conductorSearchQuery"
                                type="text"
                                required
                                @focus="showConductorDropdown = true"
                                @blur="handleConductorBlur"
                                placeholder="Buscar conductor o supervisor por nombre, DNI o móvil..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:bg-gray-800 dark:text-white"
                            />
                            <input type="hidden" v-model="form.conductor_id" />

                            <!-- Dropdown Autocompletado -->
                            <div 
                                v-if="showConductorDropdown && filteredConductores.length"
                                class="absolute left-0 right-0 z-10 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:bg-gray-800 dark:border-gray-700"
                            >
                                <button
                                    v-for="c in filteredConductores"
                                    :key="c.id"
                                    type="button"
                                    @mousedown="selectConductor(c)"
                                    class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-55 transition-all text-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 flex items-center justify-between"
                                >
                                    <div>
                                        <p class="font-bold flex items-center gap-1.5">
                                            {{ c.nombre }}
                                            <span 
                                                class="rounded px-1.5 py-0.2 text-[8px] font-extrabold uppercase tracking-wide border"
                                                :class="(c.categoria_label || 'Conductor').includes('Supervisor') || (c.categoria_label || 'Conductor').includes('Supervisión')
                                                    ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'
                                                    : 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'"
                                            >
                                                {{ c.categoria_label || 'Conductor' }}
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-400">DNI: {{ c.dni }}</p>
                                    </div>
                                    <span v-if="c.unidad_codigo" class="rounded bg-emerald-500/10 px-2 py-0.5 text-[10px] font-black text-emerald-600 dark:text-[#00ff66]">
                                        Móvil #{{ c.unidad_codigo }}
                                    </span>
                                </button>
                            </div>
                            <p v-if="form.errors.conductor_id" class="text-xs text-red-500 mt-1">{{ form.errors.conductor_id }}</p>
                        </div>

                        <!-- Cuotas de Servicios -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-alo-black dark:text-white">💰 Serv. Negociable *</label>
                                <select v-model="form.negociable_count" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:bg-gray-800 dark:text-white">
                                    <option v-for="n in 6" :key="n-1" :value="n-1">{{ n-1 }} servicios</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-alo-black dark:text-white">🏢 Serv. Corporativos *</label>
                                <select v-model="form.corporativo_count" class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:bg-gray-800 dark:text-white">
                                    <option v-for="n in 6" :key="n-1" :value="n-1">{{ n-1 }} servicios</option>
                                </select>
                            </div>
                        </div>

                        <!-- Categoría Corporativa (si aplica) -->
                        <div v-if="form.corporativo_count > 0">
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Ruta Corporativa Asignada *</label>
                            <select v-model="form.categoria_corporativo" required class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:bg-gray-800 dark:text-white">
                                <option value="" disabled>Selecciona una ruta...</option>
                                <option value="san_pablo">San Pablo</option>
                                <option value="mitsui">Mitsui</option>
                                <option value="cerro_verde">Cerro Verde</option>
                            </select>
                        </div>

                        <!-- Motivo / Descripción -->
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Motivo de la Sanción *</label>
                            <textarea
                                v-model="form.motivo"
                                rows="3"
                                required
                                maxlength="1000"
                                placeholder="Detalla el por qué de la sanción obligatoria de servicios..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:bg-gray-800 dark:text-white"
                            ></textarea>
                            <p v-if="form.errors.motivo" class="text-xs text-red-500 mt-1">{{ form.errors.motivo }}</p>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showRegModal = false"
                                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 py-2 text-sm font-semibold text-gray-650 dark:text-gray-350 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 py-2 text-sm font-bold text-white hover:from-red-700 hover:to-rose-700 disabled:opacity-50">
                                {{ form.processing ? 'Registrando...' : 'Registrar Sanción' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </Alo45Layout>
</template>

<style scoped>
.pending {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.ok {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.fail {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
