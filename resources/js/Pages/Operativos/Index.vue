<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const props = defineProps({
    operativos: Object,
    filters: Object,
    stats: Object,
});

// ── Búsqueda y Filtros ──────────────────────────────────────
const searchInput = ref(props.filters.search || '');
let searchTimeout = null;

const applyFilters = () => {
    router.get(route('operativos.index'), {
        search: searchInput.value.trim() || undefined,
    }, { preserveState: true, replace: true });
};

watch(searchInput, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

// ── Registrar Operativo ─────────────────────────────────────
const showRegModal = ref(false);
const regForm = useForm({
    nombre: '',
    fecha: new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16), // datetime-local format: YYYY-MM-DDTHH:MM
    descripcion: '',
});

const openRegModal = () => {
    regForm.reset();
    regForm.fecha = new Date().toLocaleString('sv-SE').slice(0, 16).replace(' ', 'T');
    showRegModal.value = true;
};

const submitOperativo = () => {
    regForm.post(route('operativos.store'), {
        onSuccess: () => {
            showRegModal.value = false;
            regForm.reset();
        }
    });
};

const deleteOperativo = (id) => {
    if (confirm('¿Seguro que deseas eliminar este operativo? Las faltas y papeletas asociadas no se borrarán, pero perderán su vinculación a este operativo.')) {
        router.delete(route('operativos.destroy', id), {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    // Format "YYYY-MM-DD HH:MM:SS" to "DD/MM/YYYY HH:MM"
    const t = dateStr.split(/[T ]/);
    const datePart = t[0];
    const timePart = t[1] ? t[1].slice(0, 5) : '';
    const parts = datePart.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]} ${timePart}`;
    }
    return dateStr;
};

const truncateText = (text, len = 80) => {
    if (!text) return '—';
    return text.length > len ? text.slice(0, len) + '...' : text;
};
</script>

<template>
    <Alo45Layout>
        <Head title="Control de Operativos — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Módulo de Supervisión</p>
                    <h1 class="mt-1 text-2xl font-black text-white">Control de Operativos</h1>
                    <p class="mt-1 text-sm text-gray-300/80">
                        Gestiona operativos de control vehicular, documenta incidencias generales y asocia las unidades sancionadas.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="openRegModal"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-emerald-500/25"
                    >
                        <span>+ Registrar Operativo</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- KPI Glass Cards -->
        <div class="mb-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="kpi-glass kpi-glow-emerald kpi-border-emerald rounded-2xl p-6 relative overflow-hidden bg-white dark:bg-gradient-to-br dark:from-neutral-900 dark:to-emerald-950/20 border border-gray-100 dark:border-neutral-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Total Operativos</p>
                        <h3 class="stat-number mt-2 text-3xl font-black text-gray-900 dark:text-emerald-400 dark:drop-shadow-[0_0_8px_rgba(52,211,153,0.35)]">{{ stats.total_operativos }}</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20 dark:text-emerald-400 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="absolute bottom-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>
            </div>

            <div class="kpi-glass kpi-glow-orange kpi-border-orange rounded-2xl p-6 relative overflow-hidden bg-white dark:bg-gradient-to-br dark:from-neutral-900 dark:to-orange-950/20 border border-gray-100 dark:border-neutral-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Reportados del Mes</p>
                        <h3 class="stat-number mt-2 text-3xl font-black text-gray-900 dark:text-orange-400 dark:drop-shadow-[0_0_8px_rgba(249,115,22,0.35)]">{{ stats.unidades_reportadas_mes }}</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10 text-orange-500 dark:bg-orange-500/20 dark:text-orange-400 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <div class="absolute bottom-0 right-0 w-24 h-24 bg-orange-500/5 rounded-full blur-2xl pointer-events-none"></div>
            </div>

            <div class="kpi-glass kpi-glow-red kpi-border-red rounded-2xl p-6 relative overflow-hidden bg-white dark:bg-gradient-to-br dark:from-neutral-900 dark:to-red-950/20 border border-gray-100 dark:border-neutral-800 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Papeletas en Operativo (Mes)</p>
                        <h3 class="stat-number mt-2 text-3xl font-black text-gray-900 dark:text-red-400 dark:drop-shadow-[0_0_8px_rgba(239,68,68,0.35)]">{{ stats.papeletas_mes }}</h3>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-500 dark:bg-red-500/20 dark:text-red-400 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="absolute bottom-0 right-0 w-24 h-24 bg-red-500/5 rounded-full blur-2xl pointer-events-none"></div>
            </div>
        </div>

        <!-- Buscador y Tabla -->
        <div class="bg-white/80 dark:bg-gradient-to-b dark:from-neutral-900/95 dark:to-neutral-950/95 rounded-2xl border border-gray-100 dark:border-neutral-800/80 p-6 shadow-xl shadow-gray-200/20 dark:shadow-black/40">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-grow max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-neutral-500">
                        <svg class="h-4.5 w-4.5 search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Buscar por nombre, descripción o fecha..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-white/70 dark:bg-neutral-950/70 text-sm font-semibold text-gray-900 dark:text-white focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                    />
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-neutral-800/80">
                <table class="w-full border-collapse text-left text-sm text-gray-700 dark:text-neutral-300">
                    <thead class="bg-gray-50/50 dark:bg-neutral-950/40 text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-400 border-b border-gray-100 dark:border-neutral-800">
                        <tr>
                            <th class="px-6 py-4">Fecha y Hora</th>
                            <th class="px-6 py-4">Nombre / Lugar</th>
                            <th class="px-6 py-4">Incidencias</th>
                            <th class="px-6 py-4 text-center">Faltas</th>
                            <th class="px-6 py-4 text-center">Papeletas</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                        <tr v-if="operativos.data.length === 0" class="hover:bg-transparent">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 dark:text-neutral-500 font-bold">
                                No se encontraron operativos registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="op in operativos.data"
                            :key="op.id"
                            class="hover:bg-emerald-500/5 dark:hover:bg-emerald-500/10 transition duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 dark:bg-neutral-800/80 px-2.5 py-1 font-mono text-xs font-semibold text-gray-700 dark:text-neutral-300">
                                    📅 {{ formatDate(op.fecha) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-extrabold text-gray-900 dark:text-white block text-sm tracking-tight">{{ op.nombre }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate">
                                <span class="text-xs text-gray-600 dark:text-neutral-300 font-medium italic block" :title="op.descripcion">
                                    {{ op.descripcion || 'Sin incidencias reportadas' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="op.faltas_count > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-black text-amber-600 dark:text-amber-400 border border-amber-500/10">
                                    ⚠️ {{ op.faltas_count }}
                                </span>
                                <span v-else class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-gray-100 dark:bg-neutral-800 text-[10px] font-bold text-gray-400 dark:text-neutral-400">
                                    0
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="op.papeletas_count > 0" class="inline-flex items-center gap-1 rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-black text-red-600 dark:text-red-400 border border-red-500/10">
                                    💵 {{ op.papeletas_count }}
                                </span>
                                <span v-else class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-gray-100 dark:bg-neutral-800 text-[10px] font-bold text-gray-400 dark:text-neutral-400">
                                    0
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2.5">
                                    <Link
                                        :href="route('operativos.show', op.id)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-[#00ff66] border border-emerald-500/10 dark:border-[#00ff66]/10 px-3.5 py-1.5 text-xs font-bold hover:bg-emerald-600 hover:text-white dark:hover:bg-[#00ff66] dark:hover:text-black hover:scale-102 transition-all duration-200 shadow-sm"
                                    >
                                        Detalle →
                                    </Link>
                                    <button
                                        @click="deleteOperativo(op.id)"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition duration-200 hover:scale-105"
                                        title="Eliminar Operativo"
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
            <div v-if="operativos.links.length > 3" class="mt-6 flex justify-center gap-1">
                <template v-for="link in operativos.links" :key="link.label">
                    <div
                        v-if="link.url === null"
                        class="px-3.5 py-2 text-xs font-bold text-gray-400 dark:text-neutral-600 rounded-lg select-none"
                        v-html="link.label"
                    ></div>
                    <Link
                        v-else
                        :href="link.url"
                        class="px-3.5 py-2 text-xs font-black rounded-lg transition duration-200"
                        :class="link.active
                            ? 'bg-emerald-600 text-white shadow-md'
                            : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800'"
                        v-html="link.label"
                    ></Link>
                </template>
            </div>
        </div>

        <!-- Modal Registrar Operativo -->
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

                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 border border-gray-100 dark:border-neutral-800 p-6 shadow-2xl overflow-hidden z-10 text-gray-900 dark:text-white">
                    <div class="mb-4 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800 pb-3">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <span>📋 Registrar Nuevo Operativo</span>
                        </h2>
                        <button @click="showRegModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white font-black text-sm">✕</button>
                    </div>

                    <form @submit.prevent="submitOperativo" class="space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Nombre / Lugar del Operativo *</label>
                            <input
                                v-model="regForm.nombre"
                                type="text"
                                required
                                placeholder="Ej: Operativo Control Nocturno - Av. Ejército"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Fecha y Hora *</label>
                            <input
                                v-model="regForm.fecha"
                                type="datetime-local"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-gray-600 dark:text-neutral-400 mb-1.5">Incidencias / Detalles Generales</label>
                            <textarea
                                v-model="regForm.descripcion"
                                rows="4"
                                placeholder="Escribe aquí las incidencias encontradas, detalles de clima, personal asignado u observaciones generales..."
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-neutral-800 bg-gray-50/50 dark:bg-neutral-950/70 text-sm font-semibold focus:outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition"
                            ></textarea>
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
                                class="px-5 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20 transition disabled:opacity-50"
                            >
                                Registrar Operativo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Alo45Layout>
</template>
