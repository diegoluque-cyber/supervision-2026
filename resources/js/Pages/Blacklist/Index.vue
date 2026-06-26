<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    unidades: Array,
    conductores: Array,
    filters: Object,
    stats: Object,
});

// ── Filtros y Búsqueda ───────────────────────────────────────
const search = ref(props.filters.search || '');
let searchTimeout = null;

const applyFilters = () => {
    router.get(route('blacklist.index'), {
        search: search.value || undefined,
    }, { preserveState: true, replace: true });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});

// ── Gestión de Pestañas (Tabs) ────────────────────────────────
const activeTab = ref('unidades'); // 'unidades' | 'conductores'

const goSupervision = (codigo) => {
    router.get(route('supervision.index'), { search: codigo });
};
</script>

<template>
    <Alo45Layout>
        <Head title="Lista Negra — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex items-center gap-6">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-red-600/40 to-red-500/40 rounded-2xl blur-lg opacity-100 transition-opacity duration-300"></div>
                    <div class="relative h-16 w-16 shrink-0 flex items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 shadow-xl ring-2 ring-white/20">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
                <div class="relative">
                    <p class="text-sm font-medium text-red-400/90">Control Operativo Restringido</p>
                    <h1 class="text-2xl font-bold text-white">Lista Negra de Flota</h1>
                    <p class="mt-1 text-sm text-gray-300/80">
                        Consulta y verificación en tiempo real de unidades y conductores con restricción de salida
                    </p>
                </div>
            </div>
        </template>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-red-100 dark:border-red-500/20 bg-white/80 p-5 shadow-card backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Restringidos</p>
                        <p class="mt-2 text-3xl font-black text-red-600 dark:text-red-400">{{ stats.total_restringidos }}</p>
                    </div>
                    <div class="rounded-xl bg-red-100/50 dark:bg-red-500/10 p-2.5 flex items-center justify-center">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-white/80 p-5 shadow-card backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Unidades Bloqueadas</p>
                        <p class="mt-2 text-3xl font-black text-gray-800 dark:text-gray-200">{{ stats.total_unidades }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-100 dark:bg-gray-800/60 p-2.5 flex items-center justify-center">
                        <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 17a2 2 0 11-4 0 2 2 0 014 0zM9 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-amber-100 dark:border-amber-500/20 bg-white/80 p-5 shadow-card backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Conductores Bloqueados</p>
                        <p class="mt-2 text-3xl font-black text-amber-600 dark:text-amber-400">{{ stats.total_conductores }}</p>
                    </div>
                    <div class="rounded-xl bg-amber-55 dark:bg-amber-500/10 p-2.5 flex items-center justify-center">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buscador -->
        <div class="mb-6 rounded-xl bg-white/80 backdrop-blur-sm p-4 shadow-card border border-white/50">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-400 text-sm">🔍</span>
                </div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Buscar por código de unidad, placa, nombre o DNI de conductor..."
                    class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                />
            </div>
        </div>

        <div class="mb-6 flex border-b border-gray-200 dark:border-gray-800">
            <button
                @click="activeTab = 'unidades'"
                class="border-b-2 px-6 py-3 text-sm font-extrabold transition-all duration-200"
                :class="activeTab === 'unidades'
                    ? 'border-red-500 text-red-600 dark:text-red-400 dark:border-red-500'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                Unidades Bloqueadas ({{ stats.total_unidades }})
            </button>
            <button
                @click="activeTab = 'conductores'"
                class="border-b-2 px-6 py-3 text-sm font-extrabold transition-all duration-200"
                :class="activeTab === 'conductores'
                    ? 'border-red-500 text-red-600 dark:text-red-400 dark:border-red-500'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                Conductores Restringidos ({{ stats.total_conductores }})
            </button>
        </div>

        <!-- PESTAÑA: UNIDADES BLOQUEADAS -->
        <div v-if="activeTab === 'unidades'" class="bg-white rounded-xl shadow-card overflow-hidden">
            <div v-if="unidades.length === 0" class="py-16 text-center text-gray-400 dark:text-gray-500">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-500">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="font-semibold">Ninguna unidad con bloqueos o inactiva en este momento.</p>
            </div>
            
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 font-bold border-b border-gray-100 dark:bg-black/20 dark:text-gray-400 dark:border-gray-800">
                        <tr>
                            <th class="px-6 py-4">Código</th>
                            <th class="px-6 py-4">Placa</th>
                            <th class="px-6 py-4">Categoría/Zona</th>
                            <th class="px-6 py-4">Conductor</th>
                            <th class="px-6 py-4">Motivo de Bloqueo</th>
                            <th class="px-6 py-4">Detalles</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="u in unidades" :key="u.id" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-black text-red-655 dark:text-red-400">#{{ u.codigo }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-gray-800 dark:text-gray-200">{{ u.placa }}</td>
                            <td class="px-6 py-4 text-xs font-semibold text-gray-700 dark:text-gray-300">{{ u.categoria_label }}</td>
                            <td class="px-6 py-4">
                                <div v-if="u.conductor">
                                    <p class="font-bold text-gray-800 dark:text-gray-200">{{ u.conductor.nombre }}</p>
                                    <p class="text-[10px] text-gray-400">DNI: {{ u.conductor.dni }}</p>
                                </div>
                                <span v-else class="text-gray-400 italic">Sin conductor</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-950/40 px-2.5 py-0.5 text-xs font-bold text-red-800 dark:text-red-400">
                                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    {{ u.motivo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-xs text-gray-700 dark:text-gray-300" :title="u.detalles_falta">
                                {{ u.detalles_falta }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="goSupervision(u.codigo)"
                                    class="rounded-lg bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-500/10 dark:hover:bg-red-500/20 dark:text-red-400 px-3 py-1.5 text-xs font-bold transition"
                                >
                                    Ver Faltas
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PESTAÑA: CONDUCTORES RESTRINGIDOS -->
        <div v-if="activeTab === 'conductores'" class="bg-white rounded-xl shadow-card overflow-hidden">
            <div v-if="conductores.length === 0" class="py-16 text-center text-gray-400 dark:text-gray-500">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-500">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="font-semibold">Ningún conductor suspendido o inactivo.</p>
            </div>
            
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 font-bold border-b border-gray-100 dark:bg-black/20 dark:text-gray-400 dark:border-gray-800">
                        <tr>
                            <th class="px-6 py-4">Nombre</th>
                            <th class="px-6 py-4">DNI</th>
                            <th class="px-6 py-4">Teléfono</th>
                            <th class="px-6 py-4">Unidad Asignada</th>
                            <th class="px-6 py-4">Motivo / Estado</th>
                            <th class="px-6 py-4">Detalle Expulsión</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="c in conductores" :key="c.id" class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                            <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">{{ c.nombre }}</td>
                            <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">{{ c.dni }}</td>
                            <td class="px-6 py-4 text-xs text-gray-700 dark:text-gray-300">{{ c.telefono || '—' }}</td>
                            <td class="px-6 py-4">
                                <span v-if="c.unidad_codigo !== '—'" class="font-black text-alo-green">#{{ c.unidad_codigo }}</span>
                                <span v-else class="text-gray-400 italic">—</span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="c.estado === 'suspendido'
                                        ? 'bg-orange-100 text-orange-850 dark:bg-orange-950/40 dark:text-orange-400'
                                        : 'bg-red-100 text-red-850 dark:bg-red-950/40 dark:text-red-400'"
                                >
                                    {{ c.motivo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-xs text-gray-700 dark:text-gray-300" :title="c.detalles_falta">
                                {{ c.detalles_falta }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="route('conductores.index', { search: c.dni })"
                                        class="rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 px-3 py-1.5 text-xs font-bold transition"
                                    >
                                        Editar Conductor
                                    </Link>
                                    <button
                                        v-if="c.unidad_codigo !== '—'"
                                        @click="goSupervision(c.unidad_codigo)"
                                        class="rounded-lg bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-500/10 dark:hover:bg-red-500/20 dark:text-red-400 px-3 py-1.5 text-xs font-bold transition"
                                    >
                                        Ver Faltas
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Alo45Layout>
</template>
