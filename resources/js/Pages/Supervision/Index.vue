<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const page = usePage();
const successMsg = page.props.flash?.success;

const props = defineProps({
    categoria:        String,
    categorias:       Object,
    search:           String,
    unidadEncontrada: Object,
    historial:        Array,
    stats:            Object,
    tipos:            Object,
});

// ── Tabs de categoría ─────────────────────────────────────
const categoryIcons = {
    supervision: '👁',
    san_pablo:   '🚐',
    cerro_verde: '🏔',
    mitsui:      '🏭',
    otros:       '🚕',
};

const categoryImages = {
    supervision: '/img/supervision.jpg',
    san_pablo:   '/img/san_pablo.jpg',
    cerro_verde: '/img/cerro_verde.png',
    mitsui:      '/img/mitsui.png',
    otros:       '/img/alo45.jpg',
};

const goCategoria = (key) => {
    router.get(route('supervision.index'), { categoria: key }, { preserveState: false });
};

// ── Búsqueda de unidad ────────────────────────────────────
const searchInput = ref(props.search || '');

const buscar = () => {
    router.get(route('supervision.index'), {
        categoria: props.categoria,
        search: searchInput.value.trim() || undefined,
    }, { preserveState: false });
};

const onKeydown = (e) => { if (e.key === 'Enter') buscar(); };
// ── Formulario de falta ───────────────────────────────────
const showFaltaForm = ref(false);
const photoPreviews = ref([]);
const selectedFiles = ref([]);

const faltaForm = useForm({
    unidad_id:   props.unidadEncontrada?.id ?? '',
    tipo:        'observacion',
    descripcion: '',
    fotos:       [],
    categoria:   props.categoria || '',
});

watch(() => props.unidadEncontrada, (u) => {
    if (u) {
        faltaForm.unidad_id = u.id;
        faltaForm.categoria = props.categoria;
        showFaltaForm.value  = false;
        faltaForm.reset('tipo', 'descripcion', 'fotos');
        photoPreviews.value  = [];
        selectedFiles.value  = [];
    }
});

const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    files.forEach(file => {
        selectedFiles.value.push(file);
        const reader = new FileReader();
        reader.onload = (event) => {
            photoPreviews.value.push(event.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removePhoto = (idx) => {
    selectedFiles.value.splice(idx, 1);
    photoPreviews.value.splice(idx, 1);
};

const submitFalta = () => {
    faltaForm.fotos = selectedFiles.value;
    faltaForm.categoria = props.categoria;
    faltaForm.post(route('supervision.faltas.store'), {
        onSuccess: () => {
            showFaltaForm.value  = false;
            faltaForm.reset('tipo', 'descripcion', 'fotos');
            photoPreviews.value  = [];
            selectedFiles.value  = [];
        },
    });
};

// ── Lightbox para Imágenes ──────────────────────────────────
const lightbox = ref({
    open: false,
    images: [],
    index: 0,
});

const openLightbox = (images, index) => {
    lightbox.value.images = images;
    lightbox.value.index = index;
    lightbox.value.open = true;
};

const closeLightbox = () => {
    lightbox.value.open = false;
};

const nextLightbox = () => {
    lightbox.value.index = (lightbox.value.index + 1) % lightbox.value.images.length;
};

const prevLightbox = () => {
    lightbox.value.index = (lightbox.value.index - 1 + lightbox.value.images.length) % lightbox.value.images.length;
};
// ── Eliminar falta ────────────────────────────────────────
const confirmDeleteFalta = ref(null);
const doDeleteFalta = () => {
    router.delete(route('supervision.faltas.destroy', { falta: confirmDeleteFalta.value, categoria: props.categoria, search: props.search }), {
        onSuccess: () => { confirmDeleteFalta.value = null; },
    });
};

// ── Colores de tipo premium ─────────────────────────────────
const tipoColor = {
    observacion:                    'bg-amber-50/80 backdrop-blur-xl text-amber-900 border border-amber-200/50 shadow-sm dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
    falta_grave:                    'bg-orange-50/80 backdrop-blur-xl text-orange-900 border border-orange-200/50 shadow-sm dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/30',
    expulsado:                      'bg-red-50/80 backdrop-blur-xl text-red-800 border border-red-200/50 shadow-sm dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
    tarifa_negociable:              'bg-blue-50/80 backdrop-blur-xl text-blue-900 border border-blue-200/50 shadow-sm dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30',
    libre:                          'bg-emerald-50/80 backdrop-blur-xl text-emerald-900 border border-emerald-200/50 shadow-sm dark:bg-emerald-500/10 dark:text-[#00ff66] dark:border-[#00ff66]/30',
    cancela_servicios_corporativos: 'bg-purple-50/80 backdrop-blur-xl text-purple-900 border border-purple-200/50 shadow-sm dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/30',
    bloqueo_aplicativo:             'bg-rose-50/80 backdrop-blur-xl text-rose-800 border border-rose-200/50 shadow-sm dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30',
};

const tipoIcon = {
    observacion:                    '🔍',
    falta_grave:                    '⚠️',
    expulsado:                      '⛔',
    tarifa_negociable:              '💰',
    libre:                          '✅',
    cancela_servicios_corporativos: '🏢',
    bloqueo_aplicativo:             '📱',
};

// ── Exportación y Filtros de Reportes ────────────────────────
const filterDesde = ref('');
const filterHasta = ref('');

const exportarExcel = () => {
    let url = route('supervision.exportar') + `?categoria=${props.categoria}`;
    if (searchInput.value.trim()) url += `&search=${searchInput.value.trim()}`;
    if (filterDesde.value) url += `&fecha_desde=${filterDesde.value}`;
    if (filterHasta.value) url += `&fecha_hasta=${filterHasta.value}`;
    window.location.href = url;
};

const imprimirReporte = () => {
    let url = route('supervision.imprimir') + `?categoria=${props.categoria}`;
    if (searchInput.value.trim()) url += `&search=${searchInput.value.trim()}`;
    if (filterDesde.value) url += `&fecha_desde=${filterDesde.value}`;
    if (filterHasta.value) url += `&fecha_hasta=${filterHasta.value}`;
    window.open(url, '_blank');
};
</script>

<template>
    <Alo45Layout>
        <Head title="Supervisión — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex items-center gap-6">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500/30 to-teal-500/30 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-2xl shadow-xl ring-2 ring-white/20 transition-all duration-300 group-hover:scale-105">
                        <img src="/img/supervision.jpg" class="h-full w-full object-cover" />
                    </div>
                </div>
                <div class="relative">
                    <p class="text-sm font-medium text-emerald-400/90">Sistema de Supervisión</p>
                    <h1 class="text-2xl font-bold text-white">Registro de Faltas</h1>
                    <p class="mt-1 text-sm text-gray-300/80">Busca una unidad, revisa su historial y registra una falta</p>
                </div>
            </div>
        </template>

        <!-- Tabs de categoría premium -->
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <button
                v-for="(label, key) in categorias"
                :key="key"
                @click="goCategoria(key)"
                class="zone-card group relative overflow-hidden rounded-2xl text-left transition-all duration-300 hover:-translate-y-1"
                :class="categoria === key ? 'ring-2 ring-emerald-500 shadow-xl shadow-emerald-500/30' : 'shadow-lg hover:shadow-xl'
                + (categoria !== key ? ' hover:shadow-emerald-500/10' : '')"
            >
                <!-- Background image premium -->
                <div class="absolute inset-0">
                    <img :src="categoryImages[key]" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-115 group-hover:rotate-1" :class="key === 'san_pablo' ? 'object-[50%_30%]' : ''" />
                    <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/25 backdrop-blur-sm"></div>
                </div>

                <!-- 3D Watermark Text Behind Content -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden z-0">
                    <span class="text-3xl font-black uppercase tracking-tighter text-white/5 opacity-30 transition-all duration-500 group-hover:text-white/10 group-hover:scale-125 group-hover:opacity-100 group-hover:rotate-2 select-none pointer-events-none font-sans">
                        {{ key === 'otros' ? 'TARIFA' : label }}
                    </span>
                </div>

                <!-- Glow effect premium -->
                <div class="absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100 rounded-2xl"
                     :style="`box-shadow: 0 20px 40px ${key === 'supervision' ? 'rgba(6,182,212,0.3)' : key === 'san_pablo' ? 'rgba(59,130,246,0.3)' : key === 'cerro_verde' ? 'rgba(16,185,129,0.3)' : key === 'mitsui' ? 'rgba(139,92,246,0.3)' : 'rgba(99,102,241,0.3)'}`"></div>

                <!-- Content premium -->
                <div class="relative flex items-center gap-3 px-5 py-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent rounded-full"></div>
                        <div class="h-12 w-12 shrink-0 rounded-full border-2 border-white/40 shadow-lg overflow-hidden transition group-hover:scale-110 group-hover:border-white/60">
                            <img :src="categoryImages[key]" class="h-full w-full object-cover" :class="key === 'san_pablo' ? 'object-[50%_30%]' : ''" />
                        </div>
                    </div>
                    <div class="relative">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-400/80">{{ categoryIcons[key] }} Zona</p>
                        <p class="text-sm font-black text-white">{{ key === 'otros' ? 'Tarifa negociable' : label }}</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- Stats rápidas de la categoría -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7">
            <div v-for="(label, tipo) in tipos" :key="tipo"
                class="rounded-xl bg-white/80 backdrop-blur-sm p-4 shadow-card text-center border border-white/50 transition hover:shadow-md hover:-translate-y-0.5">
                <p class="text-lg font-black font-mono" :class="{
                    'text-yellow-600 dark:text-amber-400': tipo === 'observacion',
                    'text-orange-600 dark:text-orange-400': tipo === 'falta_grave',
                    'text-alo-red dark:text-red-400':    tipo === 'expulsado',
                    'text-blue-600 dark:text-blue-400':   tipo === 'tarifa_negociable',
                    'text-alo-green dark:text-emerald-400':  tipo === 'libre',
                    'text-purple-600 dark:text-purple-400': tipo === 'cancela_servicios_corporativos',
                    'text-rose-600 dark:text-rose-400':    tipo === 'bloqueo_aplicativo',
                }">{{ (stats[tipo] || 0).toLocaleString() }}</p>
                <p class="text-xs font-bold text-alo-gray-dark dark:text-gray-300 mt-0.5">{{ label }}</p>
            </div>
        </div>

        <!-- Buscador de unidad -->
        <div class="mb-6 rounded-xl bg-white/80 backdrop-blur-sm p-5 shadow-card border border-white/50 transition hover:shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-alo-green/10">
                    <svg class="h-4 w-4 text-alo-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h2 class="font-bold text-alo-black dark:text-white">Buscar unidad</h2>
            </div>
            <div class="flex gap-3">
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Ej: 1015, P112, A421..."
                    @keydown="onKeydown"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20"
                />
                <button
                    @click="buscar"
                    class="rounded-lg bg-alo-green px-6 py-2.5 text-sm font-bold text-white transition hover:bg-alo-green-dark hover:shadow-md hover:shadow-alo-green/20"
                >
                    Buscar
                </button>
            </div>
            <p v-if="search && !unidadEncontrada" class="mt-3 text-sm font-semibold text-alo-red">
                No se encontró la unidad "{{ search }}".
            </p>
        </div>

        <!-- Resultado de búsqueda: tarjeta de unidad -->
        <div v-if="unidadEncontrada" class="mb-6 overflow-hidden rounded-xl shadow-card led-neon-card">
            <div class="bg-alo-green-dark px-5 py-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">Unidad encontrada</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <h2 class="text-2xl font-black text-white">#{{ unidadEncontrada.codigo }}</h2>
                            <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-bold text-white">
                                {{ unidadEncontrada.categoria_label }}
                            </span>
                            <span v-if="unidadEncontrada.estado !== 'activo'"
                                class="rounded-full px-2.5 py-0.5 text-xs font-bold"
                                :class="unidadEncontrada.estado === 'mantenimiento'
                                    ? 'bg-yellow-400/80 text-yellow-900'
                                    : 'bg-red-400/80 text-white'"
                            >{{ unidadEncontrada.estado }}</span>
                            <span v-if="unidadEncontrada.faltas.some(f => f.tipo === 'expulsado')"
                                class="rounded-full bg-red-500/80 px-2.5 py-0.5 text-xs font-bold text-white"
                                title="Tiene faltas por expulsado">🚫 Expulsado</span>
                        </div>
                        <p class="text-sm text-white/70 font-mono mt-1">{{ unidadEncontrada.placa }}</p>
                    </div>
                    <button
                        @click="showFaltaForm = true"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-alo-green shadow transition hover:bg-alo-green hover:text-white hover:shadow-alo"
                    >
                        + Registrar falta
                    </button>
                </div>
            </div>

            <!-- Alerta de Reincidencia -->
            <div v-if="unidadEncontrada.is_reincidente" class="bg-gradient-to-r from-red-500 to-orange-600 px-5 py-3 flex items-center gap-3 animate-pulse border-b border-red-600/35">
                <span class="text-2xl">⚠️</span>
                <div>
                    <h4 class="text-sm font-black text-white">ALERTA: UNIDAD ALTAMENTE REINCIDENTE</h4>
                    <p class="text-xs text-white/95">
                        Esta unidad o su conductor asociado registran <span class="font-black underline">{{ unidadEncontrada.recent_grave_count }} faltas graves</span> en los últimos 30 días.
                    </p>
                </div>
            </div>

            <div class="bg-alo-green px-5 py-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/60 mb-1">Conductor</p>
                        <div v-if="unidadEncontrada.conductor">
                            <p class="font-bold text-white">{{ unidadEncontrada.conductor.nombre }}</p>
                            <p class="text-sm text-white/80">DNI: {{ unidadEncontrada.conductor.dni }}</p>
                            <p class="text-sm text-white/80">📞 {{ unidadEncontrada.conductor.telefono || '—' }}</p>
                        </div>
                        <p v-else class="text-sm text-white/50">Sin conductor asignado</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/60 mb-1">Vehículo</p>
                        <p class="font-bold text-white">{{ [unidadEncontrada.marca, unidadEncontrada.modelo].filter(Boolean).join(' ') || '—' }}</p>
                        <p class="text-sm text-white/80">Placa: <span class="font-mono font-semibold">{{ unidadEncontrada.placa }}</span></p>
                        <p class="text-sm text-white/80">{{ [unidadEncontrada.color, unidadEncontrada.anio].filter(Boolean).join(' · ') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-transparent px-5 pb-5">
                <h3 class="mb-3 pt-4 font-bold text-alo-black dark:text-white">
                    Historial de faltas de esta unidad
                    <span class="ml-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs text-gray-500 dark:text-gray-300">{{ unidadEncontrada.faltas.length }}</span>
                </h3>

                <div v-if="unidadEncontrada.faltas.length === 0" class="text-sm text-gray-400 dark:text-gray-300 italic">
                    Sin faltas registradas.
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="falta in unidadEncontrada.faltas"
                        :key="falta.id"
                        class="flex items-start justify-between rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-4 py-3"
                    >
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-base">{{ tipoIcon[falta.tipo] }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold" :class="tipoColor[falta.tipo]">
                                    {{ falta.tipo_label }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-300">{{ falta.fecha_falta }}</span>
                            </div>
                            <p class="text-sm text-alo-black dark:text-white">{{ falta.descripcion }}</p>
                            <!-- Evidencias fotográficas -->
                            <div v-if="falta.fotos && falta.fotos.length" class="mt-2 flex flex-wrap gap-1.5">
                                <div
                                    v-for="(foto, fIdx) in falta.fotos"
                                    :key="fIdx"
                                    class="relative h-12 w-16 cursor-pointer overflow-hidden rounded border border-gray-200 shadow-sm transition hover:scale-105"
                                    @click="openLightbox(falta.fotos, fIdx)"
                                >
                                    <img :src="'/' + foto" class="h-full w-full object-cover" />
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-300">Supervisor: {{ falta.supervisor || '—' }}</p>
                        </div>
                        <button
                            @click="confirmDeleteFalta = falta.id"
                            class="ml-3 text-xs text-gray-300 transition hover:text-alo-red"
                            title="Eliminar falta"
                        >✕</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial general de la categoría -->
        <div class="rounded-xl bg-white dark:bg-gray-900/60 shadow-card overflow-hidden">
            <div class="border-b border-gray-100 dark:border-gray-700 px-5 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h2 class="font-bold text-alo-black dark:text-white">
                    Últimas faltas — {{ categorias[categoria] }}
                    <span class="ml-1 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-normal text-gray-500 dark:text-gray-300">últimas 30</span>
                </h2>
                
                <!-- Controles de exportación premium -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <input
                            v-model="filterDesde"
                            type="date"
                            class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-alo-green focus:outline-none"
                            placeholder="Desde"
                            title="Fecha Desde"
                        />
                        <span class="text-xs text-gray-400 dark:text-gray-300">a</span>
                        <input
                            v-model="filterHasta"
                            type="date"
                            class="rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-alo-green focus:outline-none"
                            placeholder="Hasta"
                            title="Fecha Hasta"
                        />
                    </div>
                    
                    <button
                        @click="exportarExcel"
                        class="rounded-lg bg-emerald-50 dark:bg-emerald-900/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-alo-green dark:text-emerald-400 px-3 py-1.5 text-xs font-bold transition flex items-center gap-1"
                        title="Exportar a Excel"
                    >
                        <span>📥 Excel</span>
                    </button>
                    <button
                        @click="imprimirReporte"
                        class="rounded-lg bg-blue-50 dark:bg-blue-900/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 px-3 py-1.5 text-xs font-bold transition flex items-center gap-1"
                        title="Imprimir reporte / Guardar PDF"
                    >
                        <span>🖨️ PDF</span>
                    </button>
                </div>
            </div>

            <div v-if="historial.length === 0" class="py-12 text-center text-sm text-gray-400 dark:text-gray-300 italic">
                No hay faltas registradas en esta categoría.
            </div>

            <div v-else class="divide-y divide-gray-50">
                <div
                    v-for="falta in historial"
                    :key="falta.id"
                    class="flex items-start gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
                >
                    <div class="w-24 flex-shrink-0 text-center">
                        <p class="font-black text-alo-green dark:text-emerald-400 text-sm">#{{ falta.unidad_codigo }}</p>
                        <p class="text-[10px] font-mono text-gray-400 dark:text-gray-300">{{ falta.unidad_placa }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-0.5">
                            <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="falta.tipo_color">
                                {{ tipoIcon[falta.tipo] }} {{ falta.tipo_label }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-300">{{ falta.fecha_falta }}</span>
                        </div>
                        <p class="text-sm text-alo-black dark:text-white truncate">{{ falta.descripcion }}</p>
                        <!-- Evidencias fotográficas -->
                        <div v-if="falta.fotos && falta.fotos.length" class="mt-2 flex flex-wrap gap-1.5">
                            <div
                                v-for="(foto, fIdx) in falta.fotos"
                                :key="fIdx"
                                class="relative h-12 w-16 cursor-pointer overflow-hidden rounded border border-gray-200 shadow-sm transition hover:scale-105"
                                @click="openLightbox(falta.fotos, fIdx)"
                            >
                                <img :src="'/' + foto" class="h-full w-full object-cover" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-300">
                            {{ falta.conductor }}
                            <span v-if="falta.unidad_categoria_label" class="ml-1 rounded bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 text-[10px] font-semibold text-gray-500 dark:text-gray-300">{{ falta.unidad_categoria_label }}</span>
                            · Sup: {{ falta.supervisor || '—' }}
                        </p>
                    </div>
                    <button
                        @click="confirmDeleteFalta = falta.id"
                        class="ml-2 shrink-0 text-xs text-gray-300 transition hover:text-alo-red"
                        title="Eliminar falta"
                    >✕</button>
                </div>
            </div>
        </div>

        <!-- ── Modal: Registrar Falta ── -->
        <Teleport to="body">
            <div v-if="showFaltaForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-xl bg-white dark:bg-gray-900 shadow-xl border dark:border-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="font-bold text-alo-black dark:text-white">Registrar Falta</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-300">Unidad #{{ unidadEncontrada?.codigo }} — {{ unidadEncontrada?.conductor?.nombre || 'Sin conductor' }}</p>
                    </div>
                    <button @click="showFaltaForm = false" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-white">✕</button>
                </div>
                <form @submit.prevent="submitFalta" class="space-y-4 px-6 py-5">
                    <input type="hidden" v-model="faltaForm.unidad_id" />

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Tipo de falta *</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <button
                                v-for="(label, tipo) in tipos"
                                :key="tipo"
                                type="button"
                                @click="faltaForm.tipo = tipo"
                                class="rounded-lg border-2 px-3 py-2 text-sm font-semibold transition"
                                :class="faltaForm.tipo === tipo
                                    ? {
                                        observacion:       'border-yellow-400 bg-yellow-50 text-yellow-800',
                                        falta_grave:       'border-orange-400 bg-orange-50 text-orange-800',
                                        expulsado:         'border-red-400 bg-red-50 text-red-700',
                                        tarifa_negociable: 'border-blue-400 bg-blue-50 text-blue-700',
                                        libre:             'border-green-400 bg-green-50 text-green-700',
                                        cancela_servicios_corporativos: 'border-purple-400 bg-purple-50 text-purple-700',
                                        bloqueo_aplicativo: 'border-rose-400 bg-rose-50 text-rose-700',
                                    }[tipo]
                                    : 'border-gray-200 text-gray-500 hover:border-gray-300 dark:border-gray-700 dark:text-gray-400 dark:hover:border-gray-500 dark:bg-gray-800/50'"
                            >
                                {{ tipoIcon[tipo] }} {{ label }}
                            </button>
                        </div>
                        <p v-if="faltaForm.errors.tipo" class="mt-1 text-xs text-red-500">{{ faltaForm.errors.tipo }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Descripción *</label>
                        <textarea
                            v-model="faltaForm.descripcion"
                            rows="4"
                            required
                            maxlength="1000"
                            placeholder="Describe la falta o situación observada..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20 dark:bg-gray-800 dark:text-white"
                        ></textarea>
                        <p class="mt-1 text-right text-xs text-gray-400">{{ faltaForm.descripcion.length }}/1000</p>
                        <p v-if="faltaForm.errors.descripcion" class="text-xs text-red-500">{{ faltaForm.errors.descripcion }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Evidencia Fotográfica (Opcional)</label>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleFileChange"
                            class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 dark:file:bg-emerald-900/40 file:text-alo-green dark:file:text-emerald-400 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60"
                        />
                        <!-- Previews -->
                        <div v-if="photoPreviews.length" class="mt-2 grid grid-cols-4 gap-2">
                            <div v-for="(preview, idx) in photoPreviews" :key="idx" class="relative group h-16 w-16 overflow-hidden rounded-lg border">
                                <img :src="preview" class="h-full w-full object-cover" />
                                <button type="button" @click="removePhoto(idx)" class="absolute top-0.5 right-0.5 h-4 w-4 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] hover:bg-red-600">✕</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="showFaltaForm = false"
                            class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="faltaForm.processing"
                            class="flex-1 rounded-lg bg-alo-green py-2 text-sm font-bold text-white hover:bg-alo-green-dark disabled:opacity-50">
                            {{ faltaForm.processing ? 'Guardando...' : 'Guardar falta' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </Teleport>

        <!-- ── Modal: Confirmar eliminar falta ── -->
        <Teleport to="body">
            <div v-if="confirmDeleteFalta" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-xl bg-white dark:bg-gray-900 p-6 shadow-xl border dark:border-gray-800">
                <h2 class="mb-2 font-bold text-alo-black dark:text-white">¿Eliminar esta falta?</h2>
                <p class="mb-6 text-sm text-alo-gray-dark dark:text-gray-300">Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <button @click="confirmDeleteFalta = null"
                        class="flex-1 rounded-lg border border-gray-300 dark:border-gray-700 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Cancelar
                    </button>
                    <button @click="doDeleteFalta"
                        class="flex-1 rounded-lg bg-alo-red py-2 text-sm font-bold text-white hover:bg-alo-red-dark">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- Notificación de éxito flotante -->
        <div v-if="successMsg"
            class="fixed bottom-6 right-6 z-50 rounded-xl bg-alo-green px-5 py-3 text-sm font-bold text-white shadow-alo">
            ✓ {{ successMsg }}
        </div>

        <!-- Visor de Imágenes (Lightbox) -->
        <Teleport to="body">
            <div v-if="lightbox.open" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-black/95 p-4" @keydown.esc="closeLightbox" tabindex="0">
            <button @click="closeLightbox" class="absolute top-6 right-6 text-white/80 hover:text-white text-3xl font-bold z-50">✕</button>
            
            <div class="relative max-w-4xl max-h-[80vh] flex items-center justify-center">
                <!-- Anterior -->
                <button v-if="lightbox.images.length > 1" @click="prevLightbox" class="absolute -left-16 text-white/50 hover:text-white text-5xl p-2 font-bold z-50">‹</button>
                
                <img :src="'/' + lightbox.images[lightbox.index]" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-2xl" />
                
                <!-- Siguiente -->
                <button v-if="lightbox.images.length > 1" @click="nextLightbox" class="absolute -right-16 text-white/50 hover:text-white text-5xl p-2 font-bold z-50">›</button>
            </div>
            
            <p v-if="lightbox.images.length > 1" class="text-white/60 text-sm mt-4 font-semibold">
                {{ lightbox.index + 1 }} de {{ lightbox.images.length }}
            </p>
        </div>
        </Teleport>
    </Alo45Layout>
</template>
