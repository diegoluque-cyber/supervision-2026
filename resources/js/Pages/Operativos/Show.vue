<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const props = defineProps({
    operativo: Object,
    faltas: Array,
    papeletas: Array,
    intervenciones: Array,
    unidades: Array,
    tipos_faltas: Object,
});

// ── Pestañas (Tabs) ──────────────────────────────────────────
const activeTab = ref('intervenciones'); // 'intervenciones', 'faltas', 'papeletas'

// ── Modal de Edición del Operativo ───────────────────────────
const showEditModal = ref(false);
const editForm = useForm({
    nombre: props.operativo.nombre,
    fecha: props.operativo.fecha,
    descripcion: props.operativo.descripcion || '',
});

const openEditModal = () => {
    editForm.nombre = props.operativo.nombre;
    editForm.fecha = props.operativo.fecha;
    editForm.descripcion = props.operativo.descripcion || '';
    showEditModal.value = true;
};

const submitEditOperativo = () => {
    editForm.put(route('operativos.update', props.operativo.id), {
        onSuccess: () => {
            showEditModal.value = false;
        }
    });
};

// ── Modal Registrar Falta ────────────────────────────────────
const showFaltaModal = ref(false);
const faltaUnitSearch = ref('');
const showFaltaUnitDropdown = ref(false);

const faltaForm = useForm({
    unidad_id: '',
    tipo: 'observacion',
    descripcion: '',
    fotos: null,
    fecha_falta: new Date().toLocaleString('sv-SE').slice(0, 16).replace(' ', 'T'), // datetime-local format
    fecha_alarma: '',
});

const filteredFaltaUnidades = computed(() => {
    const q = faltaUnitSearch.value.trim().toUpperCase();
    if (!q) return props.unidades.slice(0, 10);
    return props.unidades.filter(u => 
        u.codigo.toUpperCase().includes(q) || 
        u.placa.toUpperCase().includes(q) ||
        u.conductor_nombre.toUpperCase().includes(q)
    ).slice(0, 10);
});

const selectFaltaUnidad = (u) => {
    faltaForm.unidad_id = u.id;
    faltaUnitSearch.value = `#${u.codigo} (${u.placa}) - ${u.conductor_nombre}`;
    showFaltaUnitDropdown.value = false;
};

const handleFaltaUnitBlur = () => {
    setTimeout(() => {
        showFaltaUnitDropdown.value = false;
    }, 250);
};

const openFaltaModal = (preselected = null) => {
    faltaForm.reset();
    faltaForm.fecha_falta = new Date().toLocaleString('sv-SE').slice(0, 16).replace(' ', 'T');
    if (preselected) {
        faltaForm.unidad_id = preselected.unidad_id;
        faltaUnitSearch.value = `#${preselected.unidad_codigo} (${preselected.unidad_placa}) - ${preselected.conductor}`;
    } else {
        faltaUnitSearch.value = '';
    }
    showFaltaModal.value = true;
};

const handleFaltaFiles = (e) => {
    faltaForm.fotos = Array.from(e.target.files);
};

const submitFalta = () => {
    faltaForm.post(route('operativos.faltas.store', props.operativo.id), {
        onSuccess: () => {
            showFaltaModal.value = false;
            faltaForm.reset();
        }
    });
};

// ── Modal Registrar Papeleta ─────────────────────────────────
const showPapeletaModal = ref(false);
const papeletaUnitSearch = ref('');
const showPapeletaUnitDropdown = ref(false);

const papeletaForm = useForm({
    unidad_id: '',
    nro_papeleta: '',
    monto: '',
    fecha_emision: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0],
    infraccion: '',
    estado_pago: 'pendiente',
});

const filteredPapeletaUnidades = computed(() => {
    const q = papeletaUnitSearch.value.trim().toUpperCase();
    if (!q) return props.unidades.slice(0, 10);
    return props.unidades.filter(u => 
        u.codigo.toUpperCase().includes(q) || 
        u.placa.toUpperCase().includes(q) ||
        u.conductor_nombre.toUpperCase().includes(q)
    ).slice(0, 10);
});

const selectPapeletaUnidad = (u) => {
    papeletaForm.unidad_id = u.id;
    papeletaUnitSearch.value = `#${u.codigo} (${u.placa}) - ${u.conductor_nombre}`;
    showPapeletaUnitDropdown.value = false;
};

const handlePapeletaUnitBlur = () => {
    setTimeout(() => {
        showPapeletaUnitDropdown.value = false;
    }, 250);
};

const openPapeletaModal = (preselected = null) => {
    papeletaForm.reset();
    papeletaForm.fecha_emision = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0];
    if (preselected) {
        papeletaForm.unidad_id = preselected.unidad_id;
        papeletaUnitSearch.value = `#${preselected.unidad_codigo} (${preselected.unidad_placa}) - ${preselected.conductor}`;
    } else {
        papeletaUnitSearch.value = '';
    }
    showPapeletaModal.value = true;
};

const submitPapeleta = () => {
    papeletaForm.post(route('operativos.papeletas.store', props.operativo.id), {
        onSuccess: () => {
            showPapeletaModal.value = false;
            papeletaForm.reset();
        }
    });
};

// ── Modal Registrar/Editar Intervención ─────────────────────────
const showIntervencionModal = ref(false);
const intervencionUnitSearch = ref('');
const showIntervencionUnitDropdown = ref(false);
const isEditingIntervencion = ref(false);
const editingIntervencionId = ref(null);

const intervencionForm = useForm({
    unidad_id: '',
    resultado: 'sin_novedad', // 'sin_novedad', 'falta', 'papeleta'
    observaciones: '',
});

const filteredIntervencionUnidades = computed(() => {
    const q = intervencionUnitSearch.value.trim().toUpperCase();
    if (!q) return props.unidades.slice(0, 10);
    return props.unidades.filter(u => 
        u.codigo.toUpperCase().includes(q) || 
        u.placa.toUpperCase().includes(q) ||
        u.conductor_nombre.toUpperCase().includes(q)
    ).slice(0, 10);
});

const selectIntervencionUnidad = (u) => {
    intervencionForm.unidad_id = u.id;
    intervencionUnitSearch.value = `#${u.codigo} (${u.placa}) - ${u.conductor_nombre}`;
    showIntervencionUnitDropdown.value = false;
};

const handleIntervencionUnitBlur = () => {
    setTimeout(() => {
        showIntervencionUnitDropdown.value = false;
    }, 250);
};

const openIntervencionModal = () => {
    isEditingIntervencion.value = false;
    editingIntervencionId.value = null;
    intervencionForm.reset();
    intervencionUnitSearch.value = '';
    showIntervencionModal.value = true;
};

const editIntervencion = (i) => {
    isEditingIntervencion.value = true;
    editingIntervencionId.value = i.id;
    intervencionForm.unidad_id = i.unidad_id;
    intervencionForm.resultado = i.resultado;
    intervencionForm.observaciones = i.observaciones;
    intervencionUnitSearch.value = `#${i.unidad_codigo} (${i.unidad_placa}) - ${i.conductor}`;
    showIntervencionModal.value = true;
};

const submitIntervencion = () => {
    if (isEditingIntervencion.value) {
        intervencionForm.put(route('operativos.intervenciones.update', editingIntervencionId.value), {
            onSuccess: () => {
                showIntervencionModal.value = false;
                intervencionForm.reset();
            }
        });
    } else {
        intervencionForm.post(route('operativos.intervenciones.store', props.operativo.id), {
            onSuccess: () => {
                showIntervencionModal.value = false;
                intervencionForm.reset();
            }
        });
    }
};

const deleteIntervencion = (id) => {
    if (confirm('¿Seguro que deseas remover esta unidad de la lista de intervenidos en el operativo?')) {
        router.delete(route('operativos.intervenciones.destroy', id), { preserveScroll: true });
    }
};

// ── Acciones de Desvinculación y Eliminación ───────────────
const unlinkFalta = (id) => {
    if (confirm('¿Seguro que deseas desvincular esta falta de este operativo? La falta seguirá existiendo en el historial general.')) {
        router.put(route('operativos.faltas.unlink', id), {}, { preserveScroll: true });
    }
};

const deleteFalta = (id) => {
    if (confirm('¿Seguro que deseas ELIMINAR permanentemente esta falta de la base de datos? Esta acción es irreversible.')) {
        router.delete(route('operativos.faltas.destroy', id), { preserveScroll: true });
    }
};

const unlinkPapeleta = (id) => {
    if (confirm('¿Seguro que deseas desvincular esta papeleta de este operativo? La papeleta seguirá existiendo en Caja.')) {
        router.put(route('operativos.papeletas.unlink', id), {}, { preserveScroll: true });
    }
};

const deletePapeleta = (id) => {
    if (confirm('¿Seguro que deseas ELIMINAR permanentemente esta papeleta de caja? Esta acción es irreversible.')) {
        router.delete(route('operativos.papeletas.destroy', id), { preserveScroll: true });
    }
};

// Formateadores
const formatMonto = (v) => {
    return Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const t = dateStr.split(/[T ]/);
    const datePart = t[0];
    const timePart = t[1] ? ' ' + t[1].slice(0, 5) : '';
    const parts = datePart.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}${timePart}`;
    }
    return dateStr;
};
</script>

<template>
    <Alo45Layout>
        <Head :title="'Operativo: ' + operativo.nombre + ' — Taxi ALÓ45'" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex-grow">
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('operativos.index')"
                            class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-white/10 text-white hover:bg-white/20 transition"
                            title="Volver al listado"
                        >
                            🔙
                        </Link>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">
                                Detalle de Operativo — {{ formatDate(operativo.fecha_label) }}
                            </p>
                            <h1 class="mt-1 text-2xl font-black text-white flex items-center gap-3">
                                {{ operativo.nombre }}
                            </h1>
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-gray-300 max-w-4xl whitespace-pre-line leading-relaxed italic bg-emerald-950/20 rounded-xl p-4 border border-emerald-900/20">
                        {{ operativo.descripcion || 'Sin descripción o incidencias registradas.' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0 self-start">
                    <button
                        @click="openEditModal"
                        class="flex items-center gap-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 px-4 py-2 text-sm font-bold text-white transition duration-200"
                    >
                        <span>✏️ Editar Info</span>
                    </button>
                    <button
                        @click="openIntervencionModal"
                        class="flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-sm font-bold text-white shadow-lg transition duration-200 hover:scale-105"
                    >
                        <span>👮 + Intervenir Unidad</span>
                    </button>
                    <button
                        @click="openFaltaModal()"
                        class="flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-orange-600 to-amber-600 px-4 py-2 text-sm font-bold text-white shadow-lg transition duration-200 hover:scale-105"
                    >
                        <span>🚨 + Registrar Falta</span>
                    </button>
                    <button
                        @click="openPapeletaModal()"
                        class="flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 px-4 py-2 text-sm font-bold text-white shadow-lg transition duration-200 hover:scale-105"
                    >
                        <span>💵 + Registrar Papeleta</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Tarjetas de Estadísticas del Operativo -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Intervenidas -->
            <div class="relative overflow-hidden rounded-2xl border border-emerald-100 dark:border-neutral-800 bg-gradient-to-br from-emerald-50/80 to-teal-50/50 dark:from-neutral-900/40 dark:to-neutral-900/40 p-5 shadow-sm hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-800/80 dark:text-neutral-400">Total Intervenidas</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-mono font-black text-emerald-800 dark:text-white">{{ intervenciones.length }}</span>
                    <span class="text-xs font-semibold text-emerald-700/80 dark:text-neutral-500">controladas</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            </div>

            <!-- Sin Novedad -->
            <div class="relative overflow-hidden rounded-2xl border border-green-100 dark:border-neutral-800 bg-gradient-to-br from-green-50/80 to-emerald-50/50 dark:from-neutral-900/40 dark:to-neutral-900/40 p-5 shadow-sm hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-green-800/80 dark:text-neutral-400">Sin Novedad</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/10 text-green-500 dark:bg-green-500/20 dark:text-green-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-mono font-black text-green-700 dark:text-[#00ff66]">
                        {{ intervenciones.filter(i => i.resultado === 'sin_novedad').length }}
                    </span>
                    <span class="text-xs font-semibold text-green-600/80 dark:text-neutral-500">limpias</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-green-500"></div>
            </div>

            <!-- Con Faltas -->
            <div class="relative overflow-hidden rounded-2xl border border-orange-100 dark:border-neutral-800 bg-gradient-to-br from-orange-50/80 to-amber-50/50 dark:from-neutral-900/40 dark:to-neutral-900/40 p-5 shadow-sm hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-orange-800/80 dark:text-neutral-400">Con Faltas</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10 text-orange-500 dark:bg-orange-500/20 dark:text-orange-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-mono font-black text-orange-700 dark:text-orange-400">
                        {{ intervenciones.filter(i => i.resultado === 'falta').length }}
                    </span>
                    <span class="text-xs font-semibold text-orange-600/80 dark:text-neutral-500">infracciones</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-orange-500"></div>
            </div>

            <!-- Con Papeletas -->
            <div class="relative overflow-hidden rounded-2xl border border-red-100 dark:border-neutral-800 bg-gradient-to-br from-red-50/80 to-rose-50/50 dark:from-neutral-900/40 dark:to-neutral-900/40 p-5 shadow-sm hover:scale-[1.02] transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-red-800/80 dark:text-neutral-400">Con Papeletas</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10 text-red-500 dark:bg-red-500/20 dark:text-red-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-mono font-black text-red-700 dark:text-red-400">
                        {{ intervenciones.filter(i => i.resultado === 'papeleta').length }}
                    </span>
                    <span class="text-xs font-semibold text-red-600/80 dark:text-neutral-500">papeletas</span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-500"></div>
            </div>
        </div>

        <!-- Tabs de Selección -->
        <div class="mb-6 border-b border-gray-200 dark:border-neutral-800 flex gap-4">
            <button
                @click="activeTab = 'intervenciones'"
                class="pb-3 text-sm font-black border-b-2 px-1 transition duration-200"
                :class="activeTab === 'intervenciones'
                    ? 'border-emerald-500 text-emerald-600 dark:text-[#00ff66]'
                    : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white'"
            >
                👮 Unidades Intervenidas ({{ intervenciones.length }})
            </button>
            <button
                @click="activeTab = 'faltas'"
                class="pb-3 text-sm font-black border-b-2 px-1 transition duration-200"
                :class="activeTab === 'faltas'
                    ? 'border-emerald-500 text-emerald-600 dark:text-[#00ff66]'
                    : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white'"
            >
                ⚠️ Faltas Registradas ({{ faltas.length }})
            </button>
            <button
                @click="activeTab = 'papeletas'"
                class="pb-3 text-sm font-black border-b-2 px-1 transition duration-200"
                :class="activeTab === 'papeletas'
                    ? 'border-emerald-500 text-emerald-600 dark:text-[#00ff66]'
                    : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white'"
            >
                🎫 Papeletas Emitidas ({{ papeletas.length }})
            </button>
        </div>

        <!-- Secciones de Contenido -->
        <div class="bg-white/80 dark:bg-neutral-900/90 rounded-2xl border border-gray-100 dark:border-neutral-800 p-6 shadow-sm">
            <!-- ── TAB 0: INTERVENCIONES ── -->
            <div v-if="activeTab === 'intervenciones'">
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-neutral-800/80">
                    <table class="w-full border-collapse text-left text-sm text-gray-700 dark:text-neutral-300">
                        <thead class="bg-gray-50/50 dark:bg-neutral-950/40 text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-400 border-b border-gray-100 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-4">Fecha/Hora</th>
                                <th class="px-6 py-4">Unidad</th>
                                <th class="px-6 py-4">Conductor</th>
                                <th class="px-6 py-4">Resultado</th>
                                <th class="px-6 py-4">Observaciones / Detalles</th>
                                <th class="px-6 py-4 text-right">Acciones de Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                            <tr v-if="intervenciones.length === 0" class="hover:bg-transparent">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-neutral-500 font-bold">
                                    Ninguna unidad intervenida en este operativo todavía.
                                </td>
                            </tr>
                            <tr v-for="i in intervenciones" :key="i.id" class="hover:bg-emerald-500/5 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-800 dark:text-neutral-100">
                                    {{ i.fecha_intervencion }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 px-2.5 py-1 text-xs font-black text-emerald-700 dark:text-emerald-400">
                                        #{{ i.unidad_codigo }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500 block mt-1 font-mono">Placa: {{ i.unidad_placa }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-neutral-200">
                                    {{ i.conductor }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="i.resultado === 'sin_novedad'" class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">
                                        🟢 Sin Novedad
                                    </span>
                                    <span v-else-if="i.resultado === 'falta'" class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400">
                                        ⚠️ Con Falta
                                    </span>
                                    <span v-else-if="i.resultado === 'papeleta'" class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">
                                        🎫 Con Papeleta
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs max-w-xs leading-relaxed italic text-gray-600 dark:text-neutral-400">
                                    {{ i.observaciones }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            v-if="i.resultado !== 'falta'"
                                            @click="openFaltaModal(i)"
                                            class="rounded-lg bg-orange-500/10 hover:bg-orange-500 px-2.5 py-1 text-xs font-bold text-orange-600 hover:text-white transition"
                                            title="Registrar Falta para esta unidad"
                                        >
                                            🚨 + Falta
                                        </button>
                                        <button
                                            v-if="i.resultado !== 'papeleta'"
                                            @click="openPapeletaModal(i)"
                                            class="rounded-lg bg-red-500/10 hover:bg-red-500 px-2.5 py-1 text-xs font-bold text-red-600 hover:text-white transition"
                                            title="Registrar Papeleta para esta unidad"
                                        >
                                            💵 + Papeleta
                                        </button>
                                        <button
                                            @click="editIntervencion(i)"
                                            class="rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 px-2.5 py-1 text-xs font-bold text-gray-600 dark:text-neutral-350 transition"
                                            title="Editar control"
                                        >
                                            ✏️ Editar
                                        </button>
                                        <button
                                            @click="deleteIntervencion(i.id)"
                                            class="rounded-lg bg-red-500/10 hover:bg-red-500 px-2.5 py-1 text-xs font-bold text-red-600 hover:text-white transition"
                                            title="Remover de la lista"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 1: FALTAS ── -->
            <div v-if="activeTab === 'faltas'">
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-neutral-800/80">
                    <table class="w-full border-collapse text-left text-sm text-gray-700 dark:text-neutral-300">
                        <thead class="bg-gray-50/50 dark:bg-neutral-950/40 text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-400 border-b border-gray-100 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4">Unidad</th>
                                <th class="px-6 py-4">Conductor</th>
                                <th class="px-6 py-4">Tipo</th>
                                <th class="px-6 py-4">Incidencia / Descripción</th>
                                <th class="px-6 py-4">Evidencia</th>
                                <th class="px-6 py-4">Supervisor</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                            <tr v-if="faltas.length === 0" class="hover:bg-transparent">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-neutral-500 font-bold">
                                    Ninguna falta registrada en este operativo.
                                </td>
                            </tr>
                            <tr v-for="f in faltas" :key="f.id" class="hover:bg-emerald-500/5 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-800 dark:text-neutral-100">
                                    {{ f.fecha_falta }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 px-2.5 py-1 text-xs font-black text-emerald-700 dark:text-emerald-400">
                                        #{{ f.unidad_codigo }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500 block mt-1 font-mono">Placa: {{ f.unidad_placa }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-neutral-200">
                                    {{ f.conductor }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider" :class="f.tipo_color">
                                        {{ f.tipo_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs max-w-xs leading-relaxed italic text-gray-600 dark:text-neutral-400">
                                    {{ f.descripcion }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-1" v-if="f.fotos && f.fotos.length > 0">
                                        <a v-for="(foto, i) in f.fotos" :key="i" :href="'/' + foto" target="_blank" class="h-8 w-8 rounded overflow-hidden border border-gray-200 dark:border-neutral-800 hover:scale-110 transition flex items-center justify-center bg-gray-100 text-[10px]">
                                            📸
                                        </a>
                                    </div>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-gray-500 dark:text-neutral-400">
                                    {{ f.supervisor }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            @click="unlinkFalta(f.id)"
                                            class="rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 px-2.5 py-1 text-xs font-bold text-gray-600 dark:text-neutral-300 transition"
                                            title="Remover de este operativo sin borrar la falta"
                                        >
                                            Quitar
                                        </button>
                                        <button
                                            @click="deleteFalta(f.id)"
                                            class="rounded-lg bg-red-500/10 hover:bg-red-500 px-2.5 py-1 text-xs font-bold text-red-600 hover:text-white transition"
                                            title="Eliminar falta permanentemente"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── TAB 2: PAPELETAS ── -->
            <div v-if="activeTab === 'papeletas'">
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-neutral-800/80">
                    <table class="w-full border-collapse text-left text-sm text-gray-700 dark:text-neutral-300">
                        <thead class="bg-gray-50/50 dark:bg-neutral-950/40 text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-400 border-b border-gray-100 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-4">Fecha Emisión</th>
                                <th class="px-6 py-4">Unidad</th>
                                <th class="px-6 py-4">Conductor</th>
                                <th class="px-6 py-4">N° Papeleta</th>
                                <th class="px-6 py-4">Infracción / Código</th>
                                <th class="px-6 py-4">Monto</th>
                                <th class="px-6 py-4">Estado Pago</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                            <tr v-if="papeletas.length === 0" class="hover:bg-transparent">
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400 dark:text-neutral-500 font-bold">
                                    Ninguna papeleta registrada en este operativo.
                                </td>
                            </tr>
                            <tr v-for="p in papeletas" :key="p.id" class="hover:bg-emerald-500/5 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-bold text-gray-800 dark:text-neutral-100">
                                    {{ p.fecha_emision }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 px-2.5 py-1 text-xs font-black text-emerald-700 dark:text-emerald-400">
                                        #{{ p.unidad_codigo }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500 block mt-1 font-mono">Placa: {{ p.unidad_placa }}</span>
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-neutral-200">
                                    {{ p.conductor }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-black text-gray-800 dark:text-white">
                                    {{ p.nro_papeleta }}
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-gray-600 dark:text-neutral-400">
                                    {{ p.infraccion || '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800 dark:text-white font-mono">
                                    S/ {{ formatMonto(p.monto) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider"
                                        :class="p.estado_pago === 'pagado'
                                            ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'
                                            : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'"
                                    >
                                        {{ p.estado_pago }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            @click="unlinkPapeleta(p.id)"
                                            class="rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 px-2.5 py-1 text-xs font-bold text-gray-600 dark:text-neutral-300 transition"
                                            title="Remover de este operativo sin borrar la papeleta"
                                        >
                                            Quitar
                                        </button>
                                        <button
                                            @click="deletePapeleta(p.id)"
                                            class="rounded-lg bg-red-500/10 hover:bg-red-500 px-2.5 py-1 text-xs font-bold text-red-600 hover:text-white transition"
                                            title="Eliminar papeleta permanentemente"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL: EDITAR OPERATIVO -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-950/60 backdrop-blur-sm" @click="showEditModal = false"></div>

                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800 p-6 shadow-2xl z-10 text-gray-900 dark:text-white">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800 pb-3">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">✏️ Editar Datos del Operativo</h2>
                        <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white font-black text-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitEditOperativo" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Nombre / Lugar *</label>
                            <input
                                v-model="editForm.nombre"
                                type="text"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Fecha y Hora *</label>
                            <input
                                v-model="editForm.fecha"
                                type="datetime-local"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Incidencias / Detalles</label>
                            <textarea
                                v-model="editForm.descripcion"
                                rows="4"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-neutral-800 pt-4 mt-6">
                            <button
                                type="button"
                                @click="showEditModal = false"
                                class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-neutral-800 dark:text-neutral-400 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20 transition disabled:opacity-50"
                            >
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- MODAL: REGISTRAR FALTA EN OPERATIVO -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showFaltaModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-950/60 backdrop-blur-sm" @click="showFaltaModal = false"></div>

                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800 p-6 shadow-2xl z-10 text-gray-900 dark:text-white max-h-[90vh] overflow-y-auto">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800 pb-3">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">🚨 Registrar Falta en este Operativo</h2>
                        <button @click="showFaltaModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white font-black text-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitFalta" class="space-y-4">
                        <!-- Buscador de Unidades -->
                        <div class="relative">
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Unidad / Móvil / Conductor *</label>
                            <input
                                v-model="faltaUnitSearch"
                                type="text"
                                required
                                @focus="showFaltaUnitDropdown = true"
                                @blur="handleFaltaUnitBlur"
                                placeholder="Escribe código de unidad, placa o nombre..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                            <!-- Dropdown buscador -->
                            <div v-show="showFaltaUnitDropdown" class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl bg-white dark:bg-neutral-950 border border-gray-200 dark:border-neutral-850 shadow-xl z-50 text-xs">
                                <div v-if="filteredFaltaUnidades.length === 0" class="px-4 py-3 text-gray-400 dark:text-neutral-500 font-bold">
                                    No se encontraron unidades...
                                </div>
                                <div
                                    v-for="u in filteredFaltaUnidades"
                                    :key="u.id"
                                    @mousedown="selectFaltaUnidad(u)"
                                    class="px-4 py-2.5 hover:bg-emerald-500/10 dark:hover:bg-emerald-500/15 cursor-pointer flex items-center justify-between border-b border-gray-100/50 dark:border-neutral-900/50"
                                >
                                    <span class="font-black text-emerald-600 dark:text-emerald-400">#{{ u.codigo }} ({{ u.placa }})</span>
                                    <span class="font-bold text-gray-600 dark:text-neutral-300">{{ u.conductor_nombre }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tipo de falta -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Tipo de Registro/Falta *</label>
                            <select
                                v-model="faltaForm.tipo"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            >
                                <option v-for="(label, val) in tipos_faltas" :key="val" :value="val">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Fecha y Hora de la Falta -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Fecha y Hora de la Incidencia *</label>
                            <input
                                v-model="faltaForm.fecha_falta"
                                type="datetime-local"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                        </div>

                        <!-- Alarma / Citación -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Fecha de Alarma / Citación (Opcional)</label>
                            <input
                                v-model="faltaForm.fecha_alarma"
                                type="date"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                            <p class="text-[10px] text-gray-400 mt-1">Si defines una fecha, se generará una notificación/alarma en el sistema para ese día.</p>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Descripción de la Falta *</label>
                            <textarea
                                v-model="faltaForm.descripcion"
                                rows="3"
                                required
                                placeholder="Detalles de la falta detectada en la unidad..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            ></textarea>
                        </div>

                        <!-- Evidencias / Fotos -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Fotos / Evidencias (Max: 3MB c/u)</label>
                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleFaltaFiles"
                                class="w-full text-xs font-bold text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-emerald-500/10 file:text-emerald-700 dark:file:bg-emerald-500/20 dark:file:text-emerald-400 hover:file:bg-emerald-500/20 transition cursor-pointer"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-neutral-800 pt-4 mt-6">
                            <button
                                type="button"
                                @click="showFaltaModal = false"
                                class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-neutral-800 dark:text-neutral-400 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="faltaForm.processing || !faltaForm.unidad_id"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold bg-orange-600 hover:bg-orange-700 text-white shadow-lg transition disabled:opacity-50"
                            >
                                Registrar Falta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- MODAL: REGISTRAR PAPELETA EN OPERATIVO -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showPapeletaModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-950/60 backdrop-blur-sm" @click="showPapeletaModal = false"></div>

                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800 p-6 shadow-2xl z-10 text-gray-900 dark:text-white">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800 pb-3">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">💵 Registrar Papeleta en este Operativo</h2>
                        <button @click="showPapeletaModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white font-black text-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitPapeleta" class="space-y-4">
                        <!-- Buscador de Unidades -->
                        <div class="relative">
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Unidad / Móvil *</label>
                            <input
                                v-model="papeletaUnitSearch"
                                type="text"
                                required
                                @focus="showPapeletaUnitDropdown = true"
                                @blur="handlePapeletaUnitBlur"
                                placeholder="Escribe código de unidad, placa o nombre..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                            <!-- Dropdown buscador -->
                            <div v-show="showPapeletaUnitDropdown" class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl bg-white dark:bg-neutral-950 border border-gray-200 dark:border-neutral-850 shadow-xl z-50 text-xs">
                                <div v-if="filteredPapeletaUnidades.length === 0" class="px-4 py-3 text-gray-400 dark:text-neutral-500 font-bold">
                                    No se encontraron unidades...
                                </div>
                                <div
                                    v-for="u in filteredPapeletaUnidades"
                                    :key="u.id"
                                    @mousedown="selectPapeletaUnidad(u)"
                                    class="px-4 py-2.5 hover:bg-emerald-500/10 dark:hover:bg-emerald-500/15 cursor-pointer flex items-center justify-between border-b border-gray-100/50 dark:border-neutral-900/50"
                                >
                                    <span class="font-black text-emerald-600 dark:text-emerald-400">#{{ u.codigo }} ({{ u.placa }})</span>
                                    <span class="font-bold text-gray-600 dark:text-neutral-300">{{ u.conductor_nombre }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Número de papeleta -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Número de Papeleta *</label>
                                <input
                                    v-model="papeletaForm.nro_papeleta"
                                    type="text"
                                    required
                                    placeholder="Ej: M-1243"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Monto de Multa (S/.) *</label>
                                <input
                                    v-model="papeletaForm.monto"
                                    type="number"
                                    step="0.01"
                                    required
                                    placeholder="0.00"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                                />
                            </div>
                        </div>

                        <!-- Infracción y fecha emisión -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Código Infracción / Detalle</label>
                                <input
                                    v-model="papeletaForm.infraccion"
                                    type="text"
                                    placeholder="Ej: G58 (Sin luces)"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Fecha Emisión *</label>
                                <input
                                    v-model="papeletaForm.fecha_emision"
                                    type="date"
                                    required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                                />
                            </div>
                        </div>

                        <!-- Estado Pago -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Estado Pago *</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold">
                                    <input type="radio" v-model="papeletaForm.estado_pago" value="pendiente" class="text-emerald-600 focus:ring-emerald-500 h-4 w-4 border-gray-300" />
                                    Pendiente
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold">
                                    <input type="radio" v-model="papeletaForm.estado_pago" value="pagado" class="text-emerald-600 focus:ring-emerald-500 h-4 w-4 border-gray-300" />
                                    Pagado (Caja)
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-neutral-800 pt-4 mt-6">
                            <button
                                type="button"
                                @click="showPapeletaModal = false"
                                class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-neutral-800 dark:text-neutral-400 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="papeletaForm.processing || !papeletaForm.unidad_id"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold bg-red-600 hover:bg-red-700 text-white shadow-lg transition disabled:opacity-50"
                            >
                                Registrar Papeleta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- MODAL: INTERVENIR UNIDAD EN OPERATIVO -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showIntervencionModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-950/60 backdrop-blur-sm" @click="showIntervencionModal = false"></div>

                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800 p-6 shadow-2xl z-10 text-gray-900 dark:text-white">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800 pb-3">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">
                            👮 {{ isEditingIntervencion ? 'Editar Intervención' : 'Intervenir Unidad' }}
                        </h2>
                        <button @click="showIntervencionModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white font-black text-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitIntervencion" class="space-y-4">
                        <!-- Buscador de Unidades -->
                        <div class="relative">
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Unidad / Móvil / Conductor *</label>
                            <input
                                v-model="intervencionUnitSearch"
                                type="text"
                                :disabled="isEditingIntervencion"
                                :required="!isEditingIntervencion"
                                @focus="showIntervencionUnitDropdown = true"
                                @blur="handleIntervencionUnitBlur"
                                placeholder="Escribe código de unidad, placa o nombre..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition disabled:opacity-50"
                            />
                            <!-- Dropdown buscador -->
                            <div v-show="showIntervencionUnitDropdown" class="absolute left-0 right-0 mt-1 max-h-52 overflow-y-auto rounded-xl bg-white dark:bg-neutral-950 border border-gray-200 dark:border-neutral-800 shadow-xl z-50 text-xs">
                                <div v-if="filteredIntervencionUnidades.length === 0" class="px-4 py-3 text-gray-400 dark:text-neutral-500 font-bold">
                                    No se encontraron unidades...
                                </div>
                                <div
                                    v-for="u in filteredIntervencionUnidades"
                                    :key="u.id"
                                    @mousedown="selectIntervencionUnidad(u)"
                                    class="px-4 py-2.5 hover:bg-emerald-500/10 dark:hover:bg-emerald-500/15 cursor-pointer flex items-center justify-between border-b border-gray-100/50 dark:border-neutral-900/50"
                                >
                                    <span class="font-black text-emerald-600 dark:text-emerald-400">#{{ u.codigo }} ({{ u.placa }})</span>
                                    <span class="font-bold text-gray-600 dark:text-neutral-300">{{ u.conductor_nombre }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Resultado de Intervención -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Resultado del Control *</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label
                                    class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition select-none"
                                    :class="intervencionForm.resultado === 'sin_novedad'
                                        ? 'border-green-500 bg-green-50/50 dark:bg-green-950/20 text-green-700 dark:text-green-400 font-black'
                                        : 'border-gray-200 dark:border-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-800 text-gray-500 dark:text-neutral-400 font-bold'"
                                >
                                    <input type="radio" v-model="intervencionForm.resultado" value="sin_novedad" class="sr-only" />
                                    <span class="text-lg mb-1">🟢</span>
                                    <span class="text-xs">Sin Novedad</span>
                                </label>
                                <label
                                    class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition select-none"
                                    :class="intervencionForm.resultado === 'falta'
                                        ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/20 text-orange-700 dark:text-orange-400 font-black'
                                        : 'border-gray-200 dark:border-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-800 text-gray-500 dark:text-neutral-400 font-bold'"
                                >
                                    <input type="radio" v-model="intervencionForm.resultado" value="falta" class="sr-only" />
                                    <span class="text-lg mb-1">⚠️</span>
                                    <span class="text-xs">Con Falta</span>
                                </label>
                                <label
                                    class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition select-none"
                                    :class="intervencionForm.resultado === 'papeleta'
                                        ? 'border-red-500 bg-red-50/50 dark:bg-red-950/20 text-red-700 dark:text-red-400 font-black'
                                        : 'border-gray-200 dark:border-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-800 text-gray-500 dark:text-neutral-400 font-bold'"
                                >
                                    <input type="radio" v-model="intervencionForm.resultado" value="papeleta" class="sr-only" />
                                    <span class="text-lg mb-1">🎫</span>
                                    <span class="text-xs">Con Papeleta</span>
                                </label>
                            </div>
                        </div>

                        <!-- Observaciones / Detalles -->
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Observaciones o Notas</label>
                            <textarea
                                v-model="intervencionForm.observaciones"
                                rows="3"
                                placeholder="Escribe detalles del estado del vehículo, documentos, o motivos del control..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 dark:border-neutral-800 pt-4 mt-6">
                            <button
                                type="button"
                                @click="showIntervencionModal = false"
                                class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-neutral-800 dark:text-neutral-400 transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="intervencionForm.processing || (!isEditingIntervencion && !intervencionForm.unidad_id)"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg transition disabled:opacity-50"
                            >
                                {{ isEditingIntervencion ? 'Guardar Cambios' : 'Registrar Intervención' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Alo45Layout>
</template>
