<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const page = usePage();
const importResult = computed(() => page.props.flash?.importResult);
const successMsg  = computed(() => page.props.flash?.success);

const props = defineProps({
    conductores: Object,
    filters: Object,
    stats: Object,
});

// ── Filtros ─────────────────────────────────────────────────
const search = ref(props.filters.search || '');
const estado = ref(props.filters.estado || '');
let searchTimeout = null;

const applyFilters = () => {
    router.get(route('conductores.index'), {
        search: search.value || undefined,
        estado: estado.value || undefined,
    }, { preserveState: true, replace: true });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});
watch(estado, () => applyFilters());

// Helper to format dates
const formatFecha = (d) => {
    if (!d) return '';
    const datePart = d.split(/[T ]/)[0];
    const parts = datePart.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return d;
};

// ── Modal agregar conductor ──────────────────────────────────
const showAdd = ref(false);
const form = useForm({
    nombre:   '',
    dni:      '',
    licencia: '',
    fecha_licencia_vencimiento: '',
    telefono: '',
    estado:   'activo',
    fecha_citacion: '',
    motivo_citacion: '',
});

const submitAdd = () => {
    form.post(route('conductores.store'), {
        onSuccess: () => {
            showAdd.value = false;
            form.reset();
        },
    });
};

// ── Modal editar conductor ───────────────────────────────────
const showEdit = ref(false);
const editingConductor = ref(null);
const editForm = useForm({
    nombre:   '',
    dni:      '',
    licencia: '',
    fecha_licencia_vencimiento: '',
    telefono: '',
    estado:   'activo',
    fecha_citacion: '',
    motivo_citacion: '',
});

const openEdit = (conductor) => {
    editingConductor.value = conductor;
    editForm.nombre = conductor.nombre;
    editForm.dni = conductor.dni;
    editForm.licencia = conductor.licencia || '';
    editForm.fecha_licencia_vencimiento = conductor.fecha_licencia_vencimiento || '';
    editForm.telefono = conductor.telefono || '';
    editForm.estado = conductor.estado;
    editForm.fecha_citacion = conductor.fecha_citacion || '';
    editForm.motivo_citacion = conductor.motivo_citacion || '';
    showEdit.value = true;
};

const submitEdit = () => {
    editForm.put(route('conductores.update', editingConductor.value.id), {
        onSuccess: () => {
            showEdit.value = false;
            editingConductor.value = null;
            editForm.reset();
        },
    });
};

// ── Eliminar conductor ───────────────────────────────────────
const confirmDelete = ref(null);
const selectedIds = ref([]);
const confirmBulkDelete = ref(false);

const allSelected = computed({
    get: () => selectedIds.value.length === props.conductores.data.length && props.conductores.data.length > 0,
    set: (val) => { selectedIds.value = val ? props.conductores.data.map(c => c.id) : []; },
});

const toggleSelect = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) selectedIds.value.push(id);
    else selectedIds.value.splice(idx, 1);
};

const doDelete = () => {
    router.delete(route('conductores.destroy', confirmDelete.value), {
        onSuccess: () => { confirmDelete.value = null; },
    });
};

const doBulkDelete = () => {
    router.post(route('conductores.destroyMultiple'), {
        ids: selectedIds.value,
    }, {
        onSuccess: () => {
            confirmBulkDelete.value = false;
            selectedIds.value = [];
        },
    });
};

const quitarCitacion = (conductorId) => {
    if (confirm('¿Seguro que deseas quitar la citación a este conductor?')) {
        router.put(route('conductores.quitar-citacion', conductorId), {}, {
            preserveScroll: true,
        });
    }
};

// ── Estilos estado premium ───────────────────────────────────
const estadoClass = (e) => ({
    activo:     'bg-emerald-50/80 backdrop-blur-xl text-emerald-900 border border-emerald-200/50 shadow-sm dark:bg-emerald-950/40 dark:text-[#00ff66] dark:border-[#00ff66]/30',
    inactivo:   'bg-gray-50/80 backdrop-blur-xl text-gray-700 border border-gray-200/50 shadow-sm dark:bg-gray-800/40 dark:text-gray-400 dark:border-gray-700/30',
    suspendido: 'bg-red-50/80 backdrop-blur-xl text-red-800 border border-red-200/50 shadow-sm dark:bg-red-950/40 dark:text-[#ff4444] dark:border-[#ff4444]/30',
}[e] || 'bg-gray-50/80 backdrop-blur-xl text-gray-700 border border-gray-200/50 shadow-sm dark:bg-gray-800/40 dark:text-gray-400 dark:border-gray-700/30');

const estadoIcon = (e) => ({
    activo:     '●',
    inactivo:   '○',
    suspendido: '✕',
}[e] || '○');

// ── Atajos y Reloj ────────────────────────────────────────
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

const imprimirPDF = () => {
    let url = route('conductores.imprimir') + `?search=${search.value || ''}&estado=${estado.value || ''}`;
    window.open(url, '_blank');
};
</script>

<template>
    <Alo45Layout>
        <Head title="Conductores — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Left Title -->
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 blur-2xl rounded-full"></div>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Recursos humanos</p>
                        <h1 class="mt-1 text-2xl font-black text-white">Listado de Conductores</h1>
                        <p class="mt-1 text-sm text-gray-300/80">
                            <span class="font-bold text-emerald-400">{{ stats.total.toLocaleString() }}</span> conductores registrados
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
                        @click="showAdd = true"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-emerald-500/25"
                    >
                        <span>+ Registrar Conductor</span>
                    </button>
                    <button
                        @click="imprimirPDF"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-white/10 border border-white/20 hover:bg-white/15 px-5 py-2.5 text-sm font-bold text-white shadow-md transition-all duration-300 hover:scale-105"
                    >
                        <span>📥 Descargar Ficha PDF</span>
                    </button>
                    
                    <div class="relative flex flex-wrap gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-auto">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-emerald-400 dark:text-[#00ff66] search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Nombre, DNI o unidad..."
                                class="w-full rounded-xl border border-white/20 bg-white/15 py-2.5 pl-9 pr-4 text-sm text-white placeholder-white/50 backdrop-blur-xl focus:bg-white focus:text-alo-black focus:outline-none dark:focus:bg-white/20 dark:focus:text-white dark:focus:placeholder-white/50 dark:focus:border-[#00ff66]/60 dark:focus:ring-1 dark:focus:ring-[#00ff66]/30 sm:w-48 transition-all duration-300"
                            />
                        </div>
                        <div class="relative w-full sm:w-auto">
                            <select
                                v-model="estado"
                                class="w-full rounded-xl border border-white/20 bg-white/15 px-4 py-2.5 text-sm text-white backdrop-blur-xl focus:bg-white focus:text-alo-black focus:outline-none dark:focus:bg-white/20 dark:focus:text-white dark:focus:border-[#00ff66]/60 dark:focus:ring-1 dark:focus:ring-[#00ff66]/30 transition-all duration-300"
                            >
                                <option value="" class="bg-white text-alo-black dark:bg-gray-900 dark:text-white">Todos los estados</option>
                                <option value="activo"     class="bg-white text-alo-black dark:bg-gray-900 dark:text-white">Activos</option>
                                <option value="inactivo"   class="bg-white text-alo-black dark:bg-gray-900 dark:text-white">Inactivos</option>
                                <option value="suspendido" class="bg-white text-alo-black dark:bg-gray-900 dark:text-white">Suspendidos</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div v-if="importResult" class="mb-4 rounded-xl border border-alo-green bg-green-50 px-4 py-3 text-sm text-alo-black">
            Importación completada:
            <strong>{{ importResult.imported_conductores }}</strong> conductores,
            <strong>{{ importResult.imported_unidades }}</strong> unidades.
            <span v-if="importResult.skipped"> ({{ importResult.skipped }} filas omitidas)</span>
        </div>

        <!-- Stats cards (horizontal) -->
        <div class="mb-6 grid grid-cols-4 gap-3">
            <div class="rounded-xl bg-white p-3 text-center shadow-card">
                <div class="mx-auto mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-alo-green/10 dark:bg-emerald-500/10">
                    <svg class="h-4 w-4 text-alo-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-alo-green">{{ stats.total.toLocaleString() }}</p>
                <p class="text-[10px] font-semibold text-gray-500">Total</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-card">
                <div class="mx-auto mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ stats.activos.toLocaleString() }}</p>
                <p class="text-[10px] font-semibold text-gray-500">Activos</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-card">
                <div class="mx-auto mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800/60">
                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-gray-500 dark:text-gray-300">{{ stats.inactivos.toLocaleString() }}</p>
                <p class="text-[10px] font-semibold text-gray-500">Inactivos</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-card">
                <div class="mx-auto mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 dark:bg-red-500/10">
                    <svg class="h-4 w-4 text-alo-red dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-alo-red dark:text-red-400">{{ stats.suspendidos.toLocaleString() }}</p>
                <p class="text-[10px] font-semibold text-gray-500">Suspendidos</p>
            </div>
        </div>


        <!-- Barra de selección múltiple -->
        <div v-if="selectedIds.length > 0" class="sticky top-0 z-10 mb-3 flex items-center gap-3 rounded-xl bg-alo-red/10 px-4 py-2.5 backdrop-blur">
            <span class="text-sm font-semibold text-alo-red">{{ selectedIds.length }} seleccionado(s)</span>
            <button @click="confirmBulkDelete = true"
                class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-alo-red px-3 py-1.5 text-xs font-bold text-white transition hover:bg-red-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar seleccionados
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Unidad</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">DNI</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Nombres completos</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Teléfono</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="conductor in conductores.data"
                            :key="conductor.id"
                            class="transition hover:bg-green-50/50"
                            :class="selectedIds.includes(conductor.id) ? 'bg-alo-green/5' : ''"
                        >
                            <td class="w-10 whitespace-nowrap px-4 py-3 text-sm">
                                <input type="checkbox" :checked="selectedIds.includes(conductor.id)" @click="toggleSelect(conductor.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-alo-green focus:ring-alo-green dark:bg-black/60 dark:border-white/20 dark:checked:bg-[#00ff66] dark:checked:border-[#00ff66] dark:focus:ring-[#00ff66]/30" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <Link
                                    v-if="conductor.unidad"
                                    :href="route('unidades.index', { search: conductor.unidad.codigo })"
                                    class="inline-flex items-center gap-1 font-black text-alo-green hover:underline"
                                >
                                    #{{ conductor.unidad.codigo }}
                                </Link>
                                <span v-else class="text-gray-300">—</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-mono text-sm text-alo-gray-dark">{{ conductor.dni }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-alo-black dark:text-white">{{ conductor.nombre }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="text-alo-black dark:text-white">{{ conductor.telefono || '—' }}</div>
                                <div v-if="conductor.fecha_citacion" class="mt-1 rounded bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 p-1.5 text-[10px] text-amber-800 dark:text-amber-400 flex flex-col gap-1">
                                    <span>📅 Citado: {{ formatFecha(conductor.fecha_citacion) }}</span>
                                    <p class="font-normal truncate max-w-[155px] font-semibold" v-if="conductor.motivo_citacion" :title="conductor.motivo_citacion">"{{ conductor.motivo_citacion }}"</p>
                                    <button
                                        @click="quitarCitacion(conductor.id)"
                                        class="mt-1 w-full rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-0.5 px-1 transition text-[9px]"
                                    >
                                        ✓ Se reportó
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="estadoClass(conductor.estado)"
                                >
                                    <span class="text-[10px] leading-none">{{ estadoIcon(conductor.estado) }}</span>
                                    {{ conductor.estado }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="route('supervision.index', { search: conductor.unidad?.codigo })"
                                        v-if="conductor.unidad"
                                        class="inline-flex items-center gap-1 rounded-lg bg-alo-green/10 px-2.5 py-1 text-xs font-bold text-alo-green transition hover:bg-alo-green hover:text-white"
                                    >
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver
                                    </Link>
                                    <button
                                        @click="openEdit(conductor)"
                                        class="inline-flex items-center gap-1 rounded-lg bg-blue-50 dark:bg-blue-900/10 px-2.5 py-1 text-xs font-bold text-blue-600 dark:text-blue-400 transition hover:bg-blue-100 dark:hover:bg-blue-900/30"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        @click="confirmDelete = conductor.id"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-alo-red transition hover:bg-red-50"
                                    >
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="conductores.data.length === 0" class="py-16 text-center">
                <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-semibold text-gray-400">No se encontraron conductores.</p>
            </div>

            <!-- Paginación -->
            <div v-if="conductores.links && conductores.links.length > 3" class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-4 py-3">
                <p class="text-sm text-alo-gray-dark">
                    Mostrando <span class="font-bold text-alo-black dark:text-white">{{ conductores.from }}–{{ conductores.to }}</span>
                    de <span class="font-bold text-alo-black dark:text-white">{{ conductores.total.toLocaleString() }}</span>
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in conductores.links" :key="link.label">
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

        <!-- ── Modal: Agregar Conductor ── -->
        <Teleport to="body">
            <div v-if="showAdd" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="font-bold text-alo-black dark:text-white">Agregar Conductor</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-300">Complete los datos del nuevo conductor</p>
                    </div>
                    <button @click="showAdd = false; form.reset()" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitAdd" class="space-y-4 px-6 py-5">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Nombres completos *</label>
                        <input v-model="form.nombre" type="text" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                            placeholder="Ej: JUAN PABLO GOMEZ QUISPE" />
                        <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">DNI *</label>
                            <input v-model="form.dni" type="text" required maxlength="15"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                                placeholder="12345678" />
                            <p v-if="form.errors.dni" class="mt-1 text-xs text-red-500">{{ form.errors.dni }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Teléfono / Celular</label>
                            <input v-model="form.telefono" type="text" maxlength="20"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                                placeholder="987654321" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Estado *</label>
                            <select v-model="form.estado"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="suspendido">Suspendido</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Fecha de Citación</label>
                            <input v-model="form.fecha_citacion" type="date"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Motivo de Citación</label>
                        <input v-model="form.motivo_citacion" type="text" maxlength="255"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                            placeholder="Ej: Revisar estado de papeletas o citación programada" />
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showAdd = false; form.reset()"
                            class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 rounded-lg bg-alo-green py-2 text-sm font-bold text-white transition hover:bg-alo-green-dark disabled:opacity-50">
                            {{ form.processing ? 'Guardando...' : 'Guardar conductor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </Teleport>

        <!-- ── Modal: Editar Conductor ── -->
        <Teleport to="body">
            <div v-if="showEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="font-bold text-alo-black dark:text-white">Editar Conductor</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-300">Modifique los datos del conductor</p>
                    </div>
                    <button @click="showEdit = false; editForm.reset()" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4 px-6 py-5">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Nombres completos *</label>
                        <input v-model="editForm.nombre" type="text" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                            placeholder="Ej: JUAN PABLO GOMEZ QUISPE" />
                        <p v-if="editForm.errors.nombre" class="mt-1 text-xs text-red-500">{{ editForm.errors.nombre }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">DNI *</label>
                            <input v-model="editForm.dni" type="text" required maxlength="15"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                                placeholder="12345678" />
                            <p v-if="editForm.errors.dni" class="mt-1 text-xs text-red-500">{{ editForm.errors.dni }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Teléfono / Celular</label>
                            <input v-model="editForm.telefono" type="text" maxlength="20"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                                placeholder="987654321" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Estado *</label>
                            <select v-model="editForm.estado"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="suspendido">Suspendido</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Fecha de Citación</label>
                            <input v-model="editForm.fecha_citacion" type="date"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Motivo de Citación</label>
                        <input v-model="editForm.motivo_citacion" type="text" maxlength="255"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                            placeholder="Ej: Revisar estado de papeletas o citación programada" />
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showEdit = false; editForm.reset()"
                            class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="editForm.processing"
                            class="flex-1 rounded-lg bg-alo-green py-2 text-sm font-bold text-white transition hover:bg-alo-green-dark disabled:opacity-50">
                            {{ editForm.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </Teleport>

        <!-- ── Modal: Confirmar Eliminar ── -->
        <Teleport to="body">
            <div v-if="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-alo-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h2 class="mb-1 font-bold text-alo-black dark:text-white">¿Eliminar conductor?</h2>
                <p class="mb-6 text-sm text-alo-gray-dark dark:text-gray-300">
                    Esta acción no se puede deshacer. La unidad asignada quedará sin conductor.
                </p>
                <div class="flex gap-3">
                    <button @click="confirmDelete = null"
                        class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                        Cancelar
                    </button>
                    <button @click="doDelete"
                        class="flex-1 rounded-lg bg-alo-red py-2 text-sm font-bold text-white hover:bg-alo-red-dark">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- ── Modal: Confirmar Eliminación Múltiple ── -->
        <Teleport to="body">
            <div v-if="confirmBulkDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-alo-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h2 class="mb-1 font-bold text-alo-black dark:text-white">¿Eliminar {{ selectedIds.length }} conductores?</h2>
                <p class="mb-6 text-sm text-alo-gray-dark dark:text-gray-300">
                    Esta acción no se puede deshacer. Las unidades asignadas quedarán sin conductor.
                </p>
                <div class="flex gap-3">
                    <button @click="confirmBulkDelete = false"
                        class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                        Cancelar
                    </button>
                    <button @click="doBulkDelete"
                        class="flex-1 rounded-lg bg-alo-red py-2 text-sm font-bold text-white hover:bg-alo-red-dark">
                        Sí, eliminar todos
                    </button>
                </div>
            </div>
        </div>
        </Teleport>
    </Alo45Layout>
</template>
