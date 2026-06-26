<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const page = usePage();
const successMsg = computed(() => page.props.flash?.success);

const props = defineProps({
    unidades: Object,
    filters: Object,
    stats: Object,
    categorias: Object,
});

const search = ref(props.filters.search || '');
let searchTimeout = null;

const selectedIds = ref([]);
const confirmBulkDelete = ref(false);

const allSelected = computed({
    get: () => selectedIds.value.length === props.unidades.data.length && props.unidades.data.length > 0,
    set: (val) => { selectedIds.value = val ? props.unidades.data.map(u => u.id) : []; },
});

const toggleSelect = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) selectedIds.value.push(id);
    else selectedIds.value.splice(idx, 1);
};

const doBulkDelete = () => {
    router.post(route('unidades.destroyMultiple'), {
        ids: selectedIds.value,
    }, {
        onSuccess: () => {
            confirmBulkDelete.value = false;
            selectedIds.value = [];
        },
    });
};

const tabs = [
    { key: null, label: 'Todas' },
    { key: 'supervision', label: 'Supervisión' },
    { key: 'san_pablo', label: 'San Pablo' },
    { key: 'cerro_verde', label: 'Cerro Verde' },
    { key: 'mitsui', label: 'Mitsui' },
    { key: 'conductor', label: 'Tarifa Negociable' },
];

const categoryImages = {
    total:        '/img/alo45.jpg',
    supervision:  '/img/supervision.jpg',
    san_pablo:    '/img/san_pablo.jpg',
    cerro_verde:  '/img/cerro_verde.png',
    mitsui:       '/img/mitsui.png',
    conductor:    '/img/alo45.jpg',
};

const applyFilters = (categoria = props.filters.categoria) => {
    router.get(route('unidades.index'), {
        categoria: categoria || undefined,
        search: search.value || undefined,
    }, { preserveState: true, replace: true });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

const estadoClass = (estado) => ({
    activo: 'bg-emerald-50/80 backdrop-blur-xl text-emerald-900 border border-emerald-200/50 shadow-sm dark:bg-emerald-950/40 dark:text-[#00ff66] dark:border-[#00ff66]/30',
    inactivo: 'bg-gray-50/80 backdrop-blur-xl text-gray-700 border border-gray-200/50 shadow-sm dark:bg-gray-800/40 dark:text-gray-400 dark:border-gray-700/30',
    mantenimiento: 'bg-amber-50/80 backdrop-blur-xl text-amber-900 border border-amber-200/50 shadow-sm dark:bg-amber-950/40 dark:text-[#ffb703] dark:border-[#ffb703]/30',
}[estado] || 'bg-gray-50/80 backdrop-blur-xl text-gray-700 border border-gray-200/50 shadow-sm dark:bg-gray-800/40 dark:text-gray-400 dark:border-gray-700/30');

const estadoIcon = (estado) => ({
    activo: '●',
    inactivo: '○',
    mantenimiento: '⚙',
}[estado] || '○');

const statsCards = [
    { label: 'Total', key: 'total', color: 'text-alo-green', bg: 'bg-alo-green/10', icon: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1' },
    { label: 'Supervisión', key: 'supervision', color: 'text-alo-green', bg: 'bg-alo-green/10', icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
    { label: 'San Pablo', key: 'san_pablo', color: 'text-blue-600', bg: 'bg-blue-50', icon: 'M4 16V11a1 1 0 011-1h2m-3 6v3h2v-3M4 16h16m0 0v3h-2v-3M20 16V6a1 1 0 00-1-1H7a1 1 0 00-1 1v10m14 0h-2M6 16H4m2 0h2m8 0h2M9 14h2m4 0h2M9 10h6' },
    { label: 'Cerro Verde', key: 'cerro_verde', color: 'text-emerald-600', bg: 'bg-emerald-50', icon: 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9' },
    { label: 'Mitsui', key: 'mitsui', color: 'text-purple-600', bg: 'bg-purple-50', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { label: 'Tarifa Negociable', key: 'conductor', color: 'text-gray-500', bg: 'bg-gray-100', icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4' },
];

// ── Atajos y Reloj ────────────────────────────────────────
const showRegMsg = ref(false);
const timeString = ref('');
const greetingMessage = ref('');

const updateClock = () => {
    const now = new Date();
    timeString.value = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    
    const hour = now.getHours();
    if (hour >= 5 && hour < 12) {
        greetingMessage.value = 'Control de Flota ALÓ45 · Turno Mañana 🌅';
    } else if (hour >= 12 && hour < 18) {
        greetingMessage.value = 'Control de Flota ALÓ45 · Turno Tarde ☀️';
    } else {
        greetingMessage.value = 'Control de Flota ALÓ45 · Guardia de Noche 🌙';
    }
};

onMounted(() => {
    updateClock();
    const timer = setInterval(updateClock, 1000);
    onUnmounted(() => clearInterval(timer));
});

const exportarExcel = () => {
    let url = route('unidades.exportar') + `?categoria=${props.filters.categoria || ''}&search=${search.value || ''}`;
    window.location.href = url;
};
</script>

<template>
    <Alo45Layout>
        <Head title="Unidades — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Left Title -->
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 blur-2xl rounded-full"></div>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Flota ALÓ45</p>
                        <h1 class="mt-1 text-2xl font-black text-white">Listado de Unidades</h1>
                        <p class="mt-1 text-sm text-gray-300/80">
                            <span class="font-bold text-emerald-400">{{ stats.total.toLocaleString() }}</span> unidades registradas
                        </p>
                    </div>
                </div>

                <!-- Center: Reloj y Lema Corporativo -->
                <div class="flex flex-col items-center justify-center bg-white/5 border border-white/10 rounded-2xl px-6 py-2 backdrop-blur-xl max-w-sm mx-auto lg:mx-0">
                    <p class="text-xs font-bold text-emerald-300 uppercase tracking-widest text-center select-none">{{ greetingMessage }}</p>
                    <p class="text-2xl font-black text-white tracking-widest mt-1 font-mono select-none">{{ timeString }}</p>
                </div>

                <!-- Right: Barra de Atajos y Buscador -->
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <button
                        @click="showRegMsg = true"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-emerald-500/25"
                    >
                        <span>+ Registrar Unidad</span>
                    </button>
                    <button
                        @click="exportarExcel"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-white/10 border border-white/20 hover:bg-white/15 px-5 py-2.5 text-sm font-bold text-white shadow-md transition-all duration-300 hover:scale-105"
                    >
                        <span>📥 Descargar Padrón Excel</span>
                    </button>
                    
                    <div class="relative group w-full sm:w-auto">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/30 to-teal-500/30 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400 dark:text-[#00ff66] search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Código, placa o conductor..."
                                class="w-full rounded-xl border border-white/20 bg-white/15 py-2.5 pl-9 pr-4 text-sm text-white placeholder-white/50 backdrop-blur-xl focus:bg-white focus:text-alo-black focus:outline-none focus:placeholder-gray-400 dark:focus:bg-white/20 dark:focus:text-white dark:focus:placeholder-white/50 dark:focus:border-[#00ff66]/60 dark:focus:ring-1 dark:focus:ring-[#00ff66]/30 sm:w-56 transition-all duration-300"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Toast: notificación flotante -->
        <div v-if="successMsg" class="mb-4 flex items-center justify-center gap-2.5 rounded-xl border border-alo-green bg-green-50 px-5 py-3.5 text-sm font-semibold text-alo-green shadow-sm">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ successMsg }}
        </div>

        <!-- Stats cards premium -->
        <div class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div
                v-for="card in statsCards"
                :key="card.key"
                class="zone-card group relative cursor-pointer overflow-hidden rounded-2xl text-center transition-all duration-300 hover:-translate-y-1"
                :class="(filters.categoria || null) === card.key ? 'ring-2 ring-emerald-500 shadow-xl shadow-emerald-500/30' : 'shadow-lg hover:shadow-xl'
                + ( (filters.categoria || null) !== card.key ? ' hover:shadow-emerald-500/10' : '')"
                @click="applyFilters(card.key)"
            >
                <!-- Background image premium -->
                <div class="absolute inset-0">
                    <img :src="categoryImages[card.key]" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-115 group-hover:rotate-1" :class="card.key === 'san_pablo' ? 'object-[50%_30%]' : ''" />
                    <div class="absolute inset-0" :class="(filters.categoria || null) === card.key ? 'bg-gradient-to-t from-emerald-600/90 via-emerald-500/60 to-emerald-400/30' : 'bg-gradient-to-t from-black/80 via-black/55 to-black/25 backdrop-blur-sm'"></div>
                </div>

                <!-- 3D Watermark Text Behind Content -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden z-0">
                    <span class="text-3xl font-black uppercase tracking-tighter text-white/5 opacity-30 transition-all duration-500 group-hover:text-white/10 group-hover:scale-125 group-hover:opacity-100 group-hover:rotate-2 select-none pointer-events-none font-sans">
                        {{ card.key === 'conductor' ? 'TARIFA NEGOCIABLE' : (card.key === 'total' ? 'TOTAL' : card.label) }}
                    </span>
                </div>

                <!-- Glow effect premium -->
                <div class="absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100 rounded-2xl"
                     :style="`box-shadow: 0 20px 40px ${card.key === 'total' ? 'rgba(6,182,212,0.3)' : card.key === 'supervision' ? 'rgba(6,182,212,0.3)' : card.key === 'san_pablo' ? 'rgba(59,130,246,0.3)' : card.key === 'cerro_verde' ? 'rgba(16,185,129,0.3)' : card.key === 'mitsui' ? 'rgba(139,92,246,0.3)' : 'rgba(99,102,241,0.3)'}`"></div>

                <!-- Content premium -->
                <div class="relative flex flex-col items-center px-4 py-5">
                    <div class="mb-1.5 h-10 w-10 rounded-full border-2 border-white/30 overflow-hidden shadow-lg">
                        <img :src="categoryImages[card.key]" class="h-full w-full object-cover" :class="card.key === 'san_pablo' ? 'object-[50%_30%]' : ''" />
                    </div>
                    <p class="text-lg font-black text-white drop-shadow-lg stat-number">{{ (stats[card.key] || 0).toLocaleString() }}</p>
                    <p class="text-[10px] font-semibold text-white/80 drop-shadow">{{ card.label }}</p>
                </div>
            </div>
        </div>

        <!-- Tabs de categoría -->
        <div class="mb-4 flex flex-wrap gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.label"
                @click="applyFilters(tab.key)"
                class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                :class="(filters.categoria || null) === tab.key
                    ? 'bg-alo-green text-white shadow-alo dark:bg-[#00ff66] dark:text-black dark:shadow-[0_0_15px_rgba(0,255,102,0.3)]'
                    : 'bg-white text-alo-black shadow-card hover:bg-alo-green/10 hover:text-alo-green dark:bg-black/40 dark:text-gray-300 dark:hover:bg-[#00ff66]/10 dark:hover:text-[#00ff66] dark:border dark:border-white/5'"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Barra de selección múltiple -->
        <div v-if="selectedIds.length > 0" class="sticky top-0 z-10 mb-3 flex items-center gap-3 rounded-xl bg-alo-red/10 px-4 py-2.5 backdrop-blur">
            <span class="text-sm font-semibold text-alo-red">{{ selectedIds.length }} seleccionada(s)</span>
            <button @click="confirmBulkDelete = true"
                class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-alo-red px-3 py-1.5 text-xs font-bold text-white transition hover:bg-red-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar seleccionadas
            </button>
            <button @click="selectedIds = []"
                class="text-xs font-semibold text-alo-red/70 hover:text-alo-red">
                Cancelar
            </button>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-xl bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-alo-green">
                        <tr>
                            <th class="w-10 px-4 py-3 text-left">
                                <input type="checkbox" v-model="allSelected"
                                    class="h-4 w-4 rounded border-white/40 bg-white/20 text-alo-green focus:ring-alo-green dark:border-emerald-500/40 dark:bg-black/40 dark:focus:ring-emerald-500/30" />
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Código</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Placa</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Categoría</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Conductor</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Vehículo</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="unidad in unidades.data"
                            :key="unidad.id"
                            class="transition hover:bg-green-50/60"
                            :class="selectedIds.includes(unidad.id) ? 'bg-alo-green/5' : ''"
                        >
                            <td class="w-10 whitespace-nowrap px-4 py-3 text-sm">
                                <input type="checkbox" :checked="selectedIds.includes(unidad.id)" @click="toggleSelect(unidad.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-alo-green focus:ring-alo-green dark:bg-black/60 dark:border-white/20 dark:checked:bg-[#00ff66] dark:checked:border-[#00ff66] dark:focus:ring-[#00ff66]/30" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-black text-alo-green">
                                #{{ unidad.codigo }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm font-semibold text-alo-black dark:text-white">{{ unidad.placa }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-alo-gray-dark">
                                {{ categorias[unidad.categoria] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-alo-black dark:text-white">
                                <span v-if="unidad.conductor" class="font-bold">{{ unidad.conductor.nombre }}</span>
                                <span v-else class="text-gray-300">—</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-alo-gray-dark">
                                {{ [unidad.marca, unidad.modelo, unidad.color, unidad.anio].filter(Boolean).join(' · ') || '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="estadoClass(unidad.estado)"
                                >
                                    <span class="text-[10px] leading-none">{{ estadoIcon(unidad.estado) }}</span>
                                    {{ unidad.estado }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <Link
                                    :href="route('supervision.index', { search: unidad.codigo })"
                                    class="inline-flex items-center gap-1 rounded-lg bg-alo-green/10 px-2.5 py-1 text-xs font-bold text-alo-green transition hover:bg-alo-green hover:text-white"
                                >
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Supervisar
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="unidades.data.length === 0" class="py-16 text-center">
                <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>
                </svg>
                <p class="font-semibold text-gray-400">No se encontraron unidades con los filtros aplicados.</p>
                <button @click="applyFilters(null); search = ''" class="mt-3 text-sm text-alo-green underline hover:no-underline">Limpiar filtros</button>
            </div>

            <!-- Paginación -->
            <div
                v-if="unidades.links && unidades.links.length > 3"
                class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-4 py-3"
            >
                <p class="text-sm text-alo-gray-dark">
                    Mostrando <span class="font-bold text-alo-black dark:text-white">{{ unidades.from }}–{{ unidades.to }}</span>
                    de <span class="font-bold text-alo-black dark:text-white">{{ unidades.total.toLocaleString() }}</span>
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in unidades.links" :key="link.label">
                        <span
                            v-if="!link.url"
                            v-html="link.label"
                            class="rounded px-3 py-1 text-sm pointer-events-none opacity-30 text-alo-gray-dark"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            v-html="link.label"
                            class="rounded px-3 py-1 text-sm transition"
                            :class="link.active
                                ? 'bg-alo-green text-white font-bold shadow-sm'
                                : 'text-alo-gray-dark hover:bg-gray-100'"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- ── Modal: Confirmar Eliminación Múltiple ── -->
        <Teleport to="body">
            <div v-if="confirmBulkDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-alo-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h2 class="mb-1 font-bold text-alo-black">¿Eliminar {{ selectedIds.length }} unidades?</h2>
                    <p class="mb-6 text-sm text-alo-gray-dark">
                        Esta acción no se puede deshacer. Los conductores asignados quedarán sin unidad.
                    </p>
                    <div class="flex gap-3">
                        <button @click="confirmBulkDelete = false"
                            class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button @click="doBulkDelete"
                            class="flex-1 rounded-lg bg-alo-red py-2 text-sm font-bold text-white hover:bg-alo-red-dark">
                            Sí, eliminar todas
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ── Modal: Registrar Unidad Info ── -->
        <Teleport to="body">
            <div v-if="showRegMsg" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl text-center">
                    <span class="text-4xl">🚐</span>
                    <h3 class="mt-3 text-lg font-black text-alo-black">Registro de Unidades</h3>
                    <p class="mt-2 text-sm text-alo-gray-dark leading-relaxed">
                        Para registrar nuevas unidades en el sistema, por favor utiliza la sección de **Importación de Excel** para cargar el listado del padrón oficial.
                    </p>
                    <div class="mt-6 flex gap-3">
                        <button @click="showRegMsg = false" class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                            Cerrar
                        </button>
                        <Link :href="route('importar.index')" class="flex-1 rounded-lg bg-alo-green py-2 text-sm font-bold text-white hover:bg-alo-green-dark flex items-center justify-center">
                            Ir a Importar
                        </Link>
                    </div>
                </div>
            </div>
        </Teleport>
    </Alo45Layout>
</template>
