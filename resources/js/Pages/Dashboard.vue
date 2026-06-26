<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name?.split(' ')[0] ?? 'Supervisor');

const props = defineProps({
    stats: Object,
    categorias: Object,
    recentActivity: Array,
});

// ── Buscador Express ────────────────────────────────────────
const searchExpressQuery = ref('');
const handleExpressSearch = () => {
    const q = searchExpressQuery.value.trim();
    if (!q) return;
    if (/^\d+$/.test(q)) {
        router.get(route('supervision.index'), { search: q });
    } else {
        router.get(route('conductores.index'), { search: q });
    }
};

// ── Saludo Dinámico ────────────────────────────────────────
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return '¡Buenos días!';
    if (hour < 19) return '¡Buenas tardes!';
    return '¡Buenas noches!';
};

// ── Contador animado ──────────────────────────────────────
const animatedStats = ref({
    total_unidades: 0,
    total_conductores: 0,
    activos: 0,
    mantenimiento: 0,
});

const animateCount = (key, target, duration = 1200) => {
    const start = 0;
    const steps = 60;
    const increment = target / steps;
    let current = start;
    let step = 0;
    const timer = setInterval(() => {
        step++;
        current = Math.min(Math.round(increment * step), target);
        animatedStats.value[key] = current;
        if (step >= steps) clearInterval(timer);
    }, duration / steps);
};

onMounted(() => {
    setTimeout(() => {
        animateCount('total_unidades',     props.stats.total_unidades);
        animateCount('total_conductores',  props.stats.total_conductores, 1400);
        animateCount('activos',            props.stats.activos, 1000);
        animateCount('mantenimiento',      props.stats.mantenimiento, 800);
    }, 300);
});

// ── Iconos de tipo de falta ────────────────────────────────
const tipoIcon = {
    observacion:        '🔍',
    falta_grave:        '⚠️',
    expulsado:          '⛔',
    tarifa_negociable:  '💰',
    libre:              '✅',
    lista_negra:        '🚫',
    cancela_servicios_corporativos: '🏢',
    bloqueo_aplicativo: '📱',
};

const tipoColor = {
    observacion:        'bg-amber-50/80 backdrop-blur-xl text-amber-900 border border-amber-200/50 shadow-sm dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
    falta_grave:        'bg-orange-50/80 backdrop-blur-xl text-orange-900 border border-orange-200/50 shadow-sm dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/30',
    expulsado:          'bg-red-500/15 text-red-600 border border-red-500/35 shadow-[0_0_8px_rgba(239,68,68,0.15)] dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/55 dark:shadow-[0_0_12px_rgba(239,68,68,0.3)] font-bold',
    tarifa_negociable:  'bg-blue-50/80 backdrop-blur-xl text-blue-900 border border-blue-200/50 shadow-sm dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30',
    libre:              'bg-emerald-50/80 backdrop-blur-xl text-emerald-900 border border-emerald-200/50 shadow-sm dark:bg-emerald-500/10 dark:text-[#00ff66] dark:border-[#00ff66]/30',
    lista_negra:        'bg-slate-50/80 backdrop-blur-xl text-slate-800 border border-slate-200/50 shadow-sm dark:bg-slate-800/40 dark:text-slate-400 dark:border-slate-700/30',
    cancela_servicios_corporativos: 'bg-purple-50/80 backdrop-blur-xl text-purple-900 border border-purple-200/50 shadow-sm dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30',
    bloqueo_aplicativo: 'bg-rose-50/80 backdrop-blur-xl text-rose-800 border border-rose-200/50 shadow-sm dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30',
};

// ── Tarjetas de categoría ────────────────────────────────
const categoryCards = [
    {
        key: 'supervision',
        label: 'Supervisión',
        desc: 'Control general de flota',
        bgImage: '/img/supervision.jpg',
        glow: 'rgba(6,182,212,0.4)',
    },
    {
        key: 'san_pablo',
        label: 'San Pablo',
        desc: 'Ruta San Pablo',
        bgImage: '/img/san_pablo.jpg',
        glow: 'rgba(59,130,246,0.4)',
    },
    {
        key: 'cerro_verde',
        label: 'Cerro Verde',
        desc: 'Minera Cerro Verde',
        bgImage: '/img/cerro_verde.png',
        glow: 'rgba(16,185,129,0.4)',
    },
    {
        key: 'mitsui',
        label: 'Mitsui',
        desc: 'Ruta empresarial',
        bgImage: '/img/mitsui.png',
        glow: 'rgba(139,92,246,0.4)',
    },
    {
        key: 'conductor',
        label: 'Tarifa Negociable',
        desc: 'Flota general',
        bgImage: '/img/alo45.jpg',
        glow: 'rgba(99,102,241,0.4)',
    },
    {
        key: 'lista_negra',
        label: 'Lista Negra',
        desc: 'Unidades restringidas',
        bgImage: '/img/lista_negra.png',
        glow: 'rgba(239,68,68,0.4)',
    },
];
</script>

<template>
    <Alo45Layout>
        <Head title="Panel — Taxi ALÓ45 Supervisión" />

        <template #header>
            <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 blur-2xl rounded-full"></div>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Sistema de Supervisión · Taxi ALÓ45</p>
                        <h1 class="mt-1 text-2xl font-black sm:text-3xl text-white">
                            {{ getGreeting() }} {{ userName }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-300/80">
                            <span class="font-bold text-emerald-400">{{ stats.total_unidades.toLocaleString() }}</span>
                            unidades en flota ·
                            <span class="font-bold text-white">{{ stats.activos_pct }}%</span> operativas
                        </p>
                        <!-- Buscador Express -->
                        <div class="mt-4 max-w-sm relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-emerald-300 dark:text-[#00ff66] search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="searchExpressQuery"
                                type="text"
                                placeholder="Buscar unidad (#) o conductor..."
                                @keyup.enter="handleExpressSearch"
                                class="w-full pl-9 pr-20 py-2 text-sm bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:outline-none focus:bg-white/15 focus:border-white/40 focus:ring-1 focus:ring-white/40 transition-all duration-200"
                            />
                            <button
                                @click="handleExpressSearch"
                                class="absolute right-1.5 top-1.5 bottom-1.5 px-3 rounded-lg bg-emerald-500 text-xs font-black text-white hover:bg-emerald-400 transition"
                            >
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/30 to-teal-500/30 blur-xl rounded-2xl transition-all duration-300 group-hover:blur-2xl"></div>
                    <div class="glass rounded-xl px-5 py-3 text-right relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-white/5"></div>
                        <div class="relative">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-300/80">Hoy</p>
                            <p class="text-sm font-bold capitalize text-white">
                                {{ new Date().toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long' }) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- KPI cards premium glassmorphism -->
        <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group relative">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/30 to-teal-500/30 rounded-2xl blur opacity-75 group-hover:opacity-100 transition duration-300"></div>
                <div class="relative kpi-glass kpi-glow-green kpi-border-green overflow-hidden rounded-2xl p-6 transition-all duration-300 group-hover:scale-105 group-hover:shadow-2xl group-hover:shadow-emerald-500/20">
                    <div class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-gradient-to-br from-emerald-400/20 to-teal-400/20 transition-transform duration-500 group-hover:scale-125"></div>
                    <div class="flex items-start justify-between relative z-10">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400/90">Total Unidades</p>
                            <p class="mt-3 text-5xl font-black text-alo-green-dark stat-number">
                                {{ animatedStats.total_unidades.toLocaleString() }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 p-3 transition group-hover:scale-110 group-hover:rotate-3">
                            <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-6 relative z-10">
                        <div class="flex justify-between text-[11px] text-gray-400/80 font-semibold mb-2">
                            <span>CAPACIDAD ACTUAL</span>
                            <span class="text-alo-green-dark">OPTIMAL</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-alo-green-dark shadow-sm dark:bg-[#00ff66]" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conductores -->
            <div class="kpi-glass kpi-glow-red kpi-border-red group relative overflow-hidden rounded-2xl p-5 animate-count delay-100">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-alo-red/8 transition-transform group-hover:scale-110"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Conductores</p>
                        <p class="mt-2 text-4xl font-black text-alo-red dark:text-red-400 stat-number animate-count delay-100">
                            {{ animatedStats.total_conductores.toLocaleString() }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-alo-red/10 dark:bg-red-500/10 p-2.5 transition group-hover:scale-105">
                        <svg class="h-5 w-5 text-alo-red dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <Link :href="route('conductores.index')"
                          class="inline-flex items-center gap-1 text-xs font-bold text-alo-green transition hover:gap-1.5 hover:underline">
                        Ver listado completo
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Activas -->
            <div class="kpi-glass kpi-glow-emerald kpi-border-emerald group relative overflow-hidden rounded-2xl p-5 animate-count delay-200">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-emerald-400/8 transition-transform group-hover:scale-110"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Activas</p>
                        <p class="mt-2 text-4xl font-black text-alo-black dark:text-white stat-number animate-count delay-200">
                            {{ animatedStats.activos.toLocaleString() }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-emerald-100 dark:bg-emerald-950/50 p-2.5 transition group-hover:scale-105">
                        <div class="h-5 w-5 flex items-center justify-center">
                            <div class="h-3.5 w-3.5 rounded-full bg-alo-green pulse-ring"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-[10px] text-gray-400 font-semibold mb-1">
                        <span>EFECTIVIDAD DE FLOTA</span>
                        <span>{{ stats.activos_pct }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-alo-green dark:from-emerald-500 dark:to-[#00ff66]"
                             :style="`width: ${stats.activos_pct}%`"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Seccion principal en dos columnas (Zonas y Actividad) -->
        <div class="grid gap-6 lg:grid-cols-3">
            
            <!-- Columna de Zonas (Ocupa 2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Título sección zonas -->
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-extrabold uppercase tracking-widest text-alo-gray-dark">Supervisión por zona</h2>
                    <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>

                <!-- Tarjetas de zona -->
                <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                    <Link
                        v-for="(card, idx) in categoryCards"
                        :key="card.key"
                        :href="card.key === 'lista_negra' ? route('blacklist.index') : route('supervision.index', { categoria: card.key })"
                        class="zone-card group relative overflow-hidden rounded-2xl p-4 sm:p-5 cursor-pointer aspect-[0.9] flex flex-col justify-between"
                    >
                        <!-- Background image with overlay -->
                        <div class="absolute inset-0">
                            <img :src="card.bgImage" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-115 group-hover:rotate-1" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/55 to-black/25"></div>
                        </div>

                        <!-- 3D Watermark Text Behind Content -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden z-0">
                            <span class="text-4xl sm:text-5xl font-black uppercase tracking-tighter text-white/5 opacity-30 transition-all duration-500 group-hover:text-white/10 group-hover:scale-125 group-hover:opacity-100 group-hover:rotate-2 select-none pointer-events-none font-sans">
                                {{ card.key === 'lista_negra' ? 'BLOQUEO' : card.label }}
                            </span>
                        </div>

                        <!-- Glow efecto en hover -->
                        <div class="absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100 rounded-2xl"
                             :style="`box-shadow: 0 16px 36px ${card.glow}`"></div>

                        <!-- Top row: Icon / Image -->
                        <div class="relative flex items-center justify-between">
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full border border-white/30 shadow-md overflow-hidden transition group-hover:scale-105">
                                <img :src="card.bgImage" class="h-full w-full object-cover" />
                            </div>
                            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-emerald-400">
                                {{ card.key === 'lista_negra' ? 'Bloqueo' : 'Zona' }}
                            </span>
                        </div>

                        <!-- Middle row: Labels and count -->
                        <div class="relative mt-2">
                            <p class="text-[8px] sm:text-[10px] font-semibold uppercase tracking-wider text-white/60 truncate">{{ card.desc }}</p>
                            <p class="text-xs sm:text-base xl:text-lg font-black text-white leading-tight mt-0.5 truncate">{{ card.label }}</p>
                            
                            <div class="mt-2 flex items-baseline gap-1">
                                <span class="text-2xl sm:text-3xl xl:text-4xl font-black text-white stat-number">
                                    {{ card.key === 'lista_negra' ? (stats.faltas_categorias.lista_negra || 0).toLocaleString() : (stats.faltas_categorias[card.key] || 0).toLocaleString() }}
                                </span>
                                <span class="text-[8px] sm:text-[10px] font-semibold text-white/50 font-sans">
                                    {{ card.key === 'lista_negra' ? 'bloqueados' : 'faltas' }}
                                </span>
                            </div>
                        </div>

                        <!-- Bottom row: Arrow -->
                        <div class="relative flex items-center gap-1 text-[10px] sm:text-xs font-bold text-white/70 transition-all duration-200 group-hover:text-white group-hover:gap-1.5">
                            <span>Supervisar</span>
                            <svg class="h-3 w-3 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </Link>
                </div>

                <!-- Acciones rápidas (Rediseñadas más elegantes) -->
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <Link
                        :href="route('supervision.index')"
                        class="btn-shimmer inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-lg transition"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Ir a Módulo Supervisión
                    </Link>
                    <Link
                        :href="route('unidades.index')"
                        class="inline-flex items-center gap-2 rounded-xl border-2 border-alo-green/60 bg-white px-6 py-3 text-sm font-bold text-alo-green transition hover:border-alo-green hover:bg-alo-green hover:text-white hover:shadow-md hover:shadow-alo-green/10"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Ver todas las unidades
                    </Link>
                    <Link
                        :href="route('importar.index')"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-500 transition hover:border-gray-300 hover:text-gray-700 hover:shadow-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Importar Excel
                    </Link>
                </div>
            </div>

            <!-- Columna de Actividad Reciente (Ocupa 1/3) -->
            <div class="space-y-6">
                <!-- Título sección actividad -->
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-extrabold uppercase tracking-widest text-alo-gray-dark">Actividad Reciente</h2>
                    <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>

                <!-- Feed de incidentes en vivo -->
                <div class="activity-feed rounded-2xl p-5 shadow-card bg-white dark:bg-gray-900/60 border dark:border-gray-800/50">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-300 mb-4 flex items-center justify-between">
                        <span>ÚLTIMOS INCIDENTES</span>
                        <span class="flex h-2 w-2 rounded-full bg-alo-green pulse-ring"></span>
                    </h3>

                    <!-- Si no hay incidentes -->
                    <div v-if="recentActivity.length === 0" class="py-12 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-alo-green shadow-inner">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-alo-black dark:text-white">Sin incidentes recientes</p>
                        <p class="text-xs text-gray-400 dark:text-gray-300 mt-1">Buen trabajo, la flota está limpia.</p>
                    </div>

                    <!-- Listado de actividad -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="incident in recentActivity"
                            :key="incident.id"
                            class="flex gap-3 pb-4 border-b border-gray-50 dark:border-gray-800/50 last:border-b-0 last:pb-0"
                        >
                            <!-- Indicador icono -->
                            <div class="mt-0.5 text-lg leading-none select-none">
                                {{ tipoIcon[incident.tipo] || '⚠️' }}
                            </div>
                            
                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-baseline gap-1.5 mb-1">
                                    <Link
                                        :href="route('supervision.index', { search: incident.unidad_codigo })"
                                        class="text-xs font-black text-alo-green hover:underline shrink-0"
                                    >
                                        Unidad #{{ incident.unidad_codigo }}
                                    </Link>
                                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold" :class="tipoColor[incident.tipo] || 'bg-gray-100'">
                                        {{ incident.tipo_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-alo-black dark:text-gray-200 font-medium leading-relaxed italic border-l-2 border-gray-100 dark:border-gray-700 pl-2 mb-1">
                                    "{{ incident.descripcion }}"
                                </p>
                                <div class="flex items-center justify-between text-[10px] text-gray-400 font-semibold">
                                    <span class="truncate pr-1">Cond: {{ incident.conductor }}</span>
                                    <span class="shrink-0">{{ incident.fecha }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </Alo45Layout>
</template>
