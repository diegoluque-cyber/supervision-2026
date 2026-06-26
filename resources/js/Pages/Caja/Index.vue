<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const page = usePage();

const props = defineProps({
    papeletas: Object,
    filters:   Object,
    stats:     Object,
    unidades:  Array, // Lista de todas las unidades para selección
});

// ── Búsqueda y Filtros ──────────────────────────────────────
const searchInput = ref(props.filters.search || '');
const activeStatus = ref(props.filters.estado || null);
let searchTimeout = null;

const applyFilters = (estado = activeStatus.value) => {
    activeStatus.value = estado;
    router.get(route('caja.index'), {
        search: searchInput.value.trim() || undefined,
        estado: estado || undefined,
    }, { preserveState: true, replace: true });
};

watch(searchInput, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

// ── Registrar Papeleta ──────────────────────────────────────
const showRegModal = ref(false);
const unitSearchQuery = ref('');
const showUnitDropdown = ref(false);

const regForm = useForm({
    unidad_id: '',
    nro_papeleta: '',
    monto: '',
    fecha_emision: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0],
    infraccion: '',
    estado_pago: 'pendiente',
});

// Filtrar unidades para el buscador interno del modal
const filteredUnidades = computed(() => {
    const q = unitSearchQuery.value.trim().toUpperCase();
    if (!q) return props.unidades.slice(0, 10);
    return props.unidades.filter(u => 
        u.codigo.toUpperCase().includes(q) || 
        u.placa.toUpperCase().includes(q) ||
        u.conductor_nombre.toUpperCase().includes(q)
    ).slice(0, 10);
});

const selectUnidad = (u) => {
    regForm.unidad_id = u.id;
    unitSearchQuery.value = `#${u.codigo} (${u.placa}) - ${u.conductor_nombre}`;
    showUnitDropdown.value = false;
};

const handleUnitBlur = () => {
    setTimeout(() => {
        showUnitDropdown.value = false;
    }, 250);
};

const openRegModal = () => {
    regForm.reset();
    regForm.fecha_emision = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().split('T')[0];
    unitSearchQuery.value = '';
    showRegModal.value = true;
};

const submitPapeleta = () => {
    regForm.post(route('papeletas.storeGeneral'), {
        onSuccess: () => {
            showRegModal.value = false;
            regForm.reset();
        }
    });
};

// ── Acciones de Pago ─────────────────────────────────────────
const processingId = ref(null);

const togglePago = (id) => {
    if (processingId.value) return;
    processingId.value = id;
    router.put(route('papeletas.toggle-pago', id), {}, {
        preserveScroll: true,
        onFinish: () => {
            processingId.value = null;
        }
    });
};

const deletePapeleta = (id) => {
    if (confirm('¿Seguro que deseas eliminar esta papeleta del sistema?')) {
        router.delete(route('papeletas.destroy', id), {
            preserveScroll: true,
        });
    }
};

// Formateadores
const formatMonto = (v) => {
    return Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    const datePart = dateStr.split(/[T ]/)[0];
    const parts = datePart.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
};
</script>

<template>
    <Alo45Layout>
        <Head title="Caja de Papeletas — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Módulo de Tesorería</p>
                    <h1 class="mt-1 text-2xl font-black text-white">Caja & Cobro de Papeletas</h1>
                    <p class="mt-1 text-sm text-gray-300/80">
                        Visualiza deudas pendientes, procesa pagos al instante y registra nuevas multas de la flota.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="openRegModal"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-emerald-500/25"
                    >
                        <span>+ Registrar Papeleta</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Stats Cards Premium -->
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Recaudado Hoy -->
            <div class="relative overflow-hidden rounded-2xl border border-emerald-500/10 bg-emerald-50/70 dark:bg-emerald-500/5 dark:border-emerald-500/10 p-5 backdrop-blur-xl shadow-sm">
                <div class="absolute -right-4 -top-4 text-emerald-500/10 dark:text-emerald-500/10 text-7xl font-bold select-none pointer-events-none">S/.</div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Recaudado Hoy</p>
                <h3 class="mt-2 text-3xl font-black text-emerald-700 dark:text-white font-mono">S/. {{ formatMonto(stats.total_pagado_hoy) }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ stats.cant_pagado_hoy }} multas cobradas hoy</p>
            </div>

            <!-- Total Pendiente -->
            <div class="relative overflow-hidden rounded-2xl border border-red-500/10 bg-red-50/70 dark:bg-red-500/5 dark:border-red-500/10 p-5 backdrop-blur-xl shadow-sm">
                <div class="absolute -right-4 -top-4 text-red-500/10 dark:text-red-500/10 text-7xl font-bold select-none pointer-events-none">S/.</div>
                <p class="text-xs font-bold uppercase tracking-wider text-red-600 dark:text-red-400">Total por Cobrar</p>
                <h3 class="mt-2 text-3xl font-black text-red-700 dark:text-white font-mono">S/. {{ formatMonto(stats.total_pendiente) }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ stats.cant_pendiente }} papeletas pendientes</p>
            </div>

            <!-- Multas Pendientes (Cantidad) -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200/50 bg-gray-50/70 dark:bg-white/5 dark:border-white/10 p-5 backdrop-blur-xl shadow-sm">
                <div class="absolute -right-2 -top-2 text-gray-900/5 dark:text-white/5 text-6xl font-bold select-none pointer-events-none">⚠️</div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cant. Pendientes</p>
                <h3 class="mt-2 text-3xl font-black text-gray-800 dark:text-white font-mono">{{ stats.cant_pendiente }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">Esperando pago en sistema</p>
            </div>

            <!-- Cobros del día (Cantidad) -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-200/50 bg-gray-50/70 dark:bg-white/5 dark:border-white/10 p-5 backdrop-blur-xl shadow-sm">
                <div class="absolute -right-2 -top-2 text-gray-900/5 dark:text-white/5 text-6xl font-bold select-none pointer-events-none">✓</div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cobros Realizados</p>
                <h3 class="mt-2 text-3xl font-black text-gray-800 dark:text-white font-mono">{{ stats.cant_pagado_hoy }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-semibold">Transacciones hoy</p>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros de Estado -->
        <div class="mb-6 rounded-xl bg-white/80 backdrop-blur-sm p-4 shadow-card border border-white/50 dark:bg-black/30 dark:border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative group w-full md:w-80">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    v-model="searchInput"
                    type="search"
                    placeholder="Buscar por unidad, placa, conductor..."
                    class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white/10 dark:bg-black/20 py-2 pl-9 pr-4 text-sm text-alo-black dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-alo-green focus:border-transparent transition-all"
                />
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                <button
                    @click="applyFilters(null)"
                    class="rounded-full px-4 py-1.5 text-xs font-bold transition whitespace-nowrap"
                    :class="activeStatus === null
                        ? 'bg-alo-green text-white shadow-sm'
                        : 'bg-white text-alo-black border dark:bg-black/40 dark:text-gray-300 dark:border-white/5 hover:bg-gray-50'"
                >
                    Todas
                </button>
                <button
                    @click="applyFilters('pendiente')"
                    class="rounded-full px-4 py-1.5 text-xs font-bold transition whitespace-nowrap"
                    :class="activeStatus === 'pendiente'
                        ? 'bg-red-500 text-white shadow-sm'
                        : 'bg-white text-alo-black border dark:bg-black/40 dark:text-gray-300 dark:border-white/5 hover:bg-gray-50'"
                >
                    Pendientes (S/. {{ formatMonto(stats.total_pendiente) }})
                </button>
                <button
                    @click="applyFilters('pagado')"
                    class="rounded-full px-4 py-1.5 text-xs font-bold transition whitespace-nowrap"
                    :class="activeStatus === 'pagado'
                        ? 'bg-emerald-600 text-white shadow-sm'
                        : 'bg-white text-alo-black border dark:bg-black/40 dark:text-gray-300 dark:border-white/5 hover:bg-gray-50'"
                >
                    Pagadas
                </button>
            </div>
        </div>

        <!-- Tabla de Papeletas -->
        <div class="overflow-hidden rounded-xl bg-white shadow-card dark:bg-gray-900/60 dark:border dark:border-white/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                    <thead class="bg-slate-900 dark:bg-black">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-white">Unidad</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-white">Nro. Papeleta</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-white">Infracción / Concepto</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-white">Fecha Emisión</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-white">Monto</th>
                            <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-white">Estado</th>
                            <th class="px-5 py-3.5 text-right text-xs font-bold uppercase tracking-wider text-white">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50 bg-white/20 dark:bg-transparent">
                        <tr
                            v-for="pap in papeletas.data"
                            :key="pap.id"
                            class="transition hover:bg-gray-50/50 dark:hover:bg-white/5"
                        >
                            <td class="whitespace-nowrap px-5 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-alo-green dark:text-emerald-400">#{{ pap.unidad?.codigo }}</span>
                                    <span class="text-xs text-gray-400 font-mono">({{ pap.unidad?.placa }})</span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 max-w-[150px] truncate" :title="pap.conductor?.nombre">
                                    {{ pap.conductor?.nombre || 'Sin conductor' }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-alo-black dark:text-white font-mono">
                                📄 {{ pap.nro_papeleta }}
                            </td>
                            <td class="px-5 py-4 text-sm text-alo-gray-dark dark:text-gray-300">
                                <span class="block max-w-[250px] truncate" :title="pap.infraccion || '—'">
                                    {{ pap.infraccion || '—' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-alo-gray-dark dark:text-gray-300">
                                {{ formatDate(pap.fecha_emision) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-black font-mono text-alo-black dark:text-white">
                                S/. {{ formatMonto(pap.monto) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider"
                                    :class="pap.estado_pago === 'pagado'
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-[#00ff66]'
                                        : 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-[#ff4444]'"
                                >
                                    ● {{ pap.estado_pago }}
                                </span>
                                <p v-if="pap.fecha_pago" class="text-[9px] text-gray-400 mt-0.5 font-mono">
                                    Pago: {{ formatDate(pap.fecha_pago) }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                <div class="flex items-center justify-end gap-3">
                                    <!-- Cobrar o Revertir -->
                                    <button
                                        v-if="pap.estado_pago === 'pendiente'"
                                        @click="togglePago(pap.id)"
                                        :disabled="processingId === pap.id"
                                        class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm transition hover:scale-105 disabled:opacity-50 disabled:pointer-events-none"
                                    >
                                        {{ processingId === pap.id ? 'Procesando...' : '💵 Cobrar' }}
                                    </button>
                                    <button
                                        v-else
                                        @click="togglePago(pap.id)"
                                        :disabled="processingId === pap.id"
                                        class="rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-3.5 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-300 transition disabled:opacity-50 disabled:pointer-events-none"
                                    >
                                        {{ processingId === pap.id ? 'Procesando...' : 'Revertir' }}
                                    </button>

                                    <!-- Eliminar -->
                                    <button
                                        @click="deletePapeleta(pap.id)"
                                        class="text-gray-300 hover:text-red-500 transition text-sm font-bold pl-1"
                                        title="Eliminar papeleta"
                                    >✕</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="papeletas.data.length === 0" class="py-16 text-center">
                <span class="text-4xl">📄</span>
                <p class="font-bold text-gray-400 mt-3">No hay papeletas registradas con los filtros aplicados.</p>
                <button @click="applyFilters(null); searchInput = ''" class="mt-2 text-sm text-alo-green underline hover:no-underline">Limpiar filtros</button>
            </div>

            <!-- Paginación -->
            <div
                v-if="papeletas.links && papeletas.links.length > 3"
                class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 dark:border-white/5 px-5 py-4"
            >
                <p class="text-sm text-alo-gray-dark dark:text-gray-400">
                    Mostrando <span class="font-bold text-alo-black dark:text-white">{{ papeletas.from }}–{{ papeletas.to }}</span>
                    de <span class="font-bold text-alo-black dark:text-white">{{ papeletas.total.toLocaleString() }}</span>
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in papeletas.links" :key="link.label">
                        <span
                            v-if="!link.url"
                            v-html="link.label"
                            class="rounded px-3 py-1 text-xs pointer-events-none opacity-30 text-alo-gray-dark dark:text-gray-400"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            v-html="link.label"
                            class="rounded px-3 py-1 text-xs transition"
                            :class="link.active
                                ? 'bg-alo-green text-white font-bold shadow-sm'
                                : 'text-alo-gray-dark dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- ── Modal: Registrar Papeleta en Caja ── -->
        <Teleport to="body">
            <div v-if="showRegModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-md rounded-xl bg-white dark:bg-gray-900 shadow-xl border dark:border-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-6 py-4">
                        <div>
                            <h2 class="font-bold text-alo-black dark:text-white">Registrar Nueva Papeleta</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-300">Registrar cobro o deuda de multa en sistema de caja</p>
                        </div>
                        <button @click="showRegModal = false" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-white">✕</button>
                    </div>
                    <form @submit.prevent="submitPapeleta" class="space-y-4 px-6 py-5">
                        <!-- Buscador de Unidad -->
                        <div class="relative">
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Unidad / Vehículo *</label>
                            <input
                                v-model="unitSearchQuery"
                                @focus="showUnitDropdown = true"
                                @blur="handleUnitBlur"
                                type="text"
                                required
                                placeholder="Escribe el código de la unidad (ej: 1015, P119)..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20 dark:bg-gray-800 dark:text-white"
                            />
                            <!-- Dropdown de Unidades Filtradas -->
                            <div v-if="showUnitDropdown" class="absolute z-50 mt-1 w-full max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                                <div
                                    v-for="u in filteredUnidades"
                                    :key="u.id"
                                    @click="selectUnidad(u)"
                                    class="cursor-pointer px-4 py-2 text-sm text-alo-black dark:text-white hover:bg-alo-green/10 transition"
                                >
                                    <span class="font-black text-alo-green">#{{ u.codigo }}</span>
                                    <span class="text-xs text-gray-400 ml-1">({{ u.placa }})</span>
                                    <span class="text-xs text-gray-500 block truncate">{{ u.conductor_nombre }}</span>
                                </div>
                                <div v-if="filteredUnidades.length === 0" class="px-4 py-2 text-xs text-gray-400 italic">
                                    No se encontraron unidades.
                                </div>
                            </div>
                            <p v-if="regForm.errors.unidad_id" class="text-xs text-red-500 mt-1">Debes seleccionar una unidad de la lista.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Nro. de Papeleta *</label>
                                <input v-model="regForm.nro_papeleta" type="text" required maxlength="50"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20 dark:bg-gray-800 dark:text-white"
                                    placeholder="Ej: M-20942" />
                                <p v-if="regForm.errors.nro_papeleta" class="text-xs text-red-500 mt-1">{{ regForm.errors.nro_papeleta }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Monto (S/.) *</label>
                                <input v-model="regForm.monto" type="number" step="0.01" min="0" required
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20 dark:bg-gray-800 dark:text-white font-mono"
                                    placeholder="150.00" />
                                <p v-if="regForm.errors.monto" class="text-xs text-red-500 mt-1">{{ regForm.errors.monto }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Fecha de Emisión *</label>
                                <input v-model="regForm.fecha_emision" type="date" required
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20 dark:bg-gray-800 dark:text-white" />
                                <p v-if="regForm.errors.fecha_emision" class="text-xs text-red-500 mt-1">{{ regForm.errors.fecha_emision }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Estado Inicial *</label>
                                <select v-model="regForm.estado_pago"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-alo-green focus:outline-none dark:bg-gray-800 dark:text-white">
                                    <option value="pendiente">Pendiente (Deuda)</option>
                                    <option value="pagado">Pagado (Cobro en Caja)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Infracción / Concepto</label>
                            <input v-model="regForm.infraccion" type="text" maxlength="255"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20 dark:bg-gray-800 dark:text-white"
                                placeholder="Ej: G-57 Estacionar en zona prohibida" />
                            <p v-if="regForm.errors.infraccion" class="text-xs text-red-500 mt-1">{{ regForm.errors.infraccion }}</p>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showRegModal = false"
                                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="regForm.processing"
                                class="flex-1 rounded-lg bg-alo-green py-2 text-sm font-bold text-white hover:bg-alo-green-dark disabled:opacity-50 transition">
                                {{ regForm.processing ? 'Registrando...' : 'Registrar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>


    </Alo45Layout>
</template>
