<script setup>
import Alo45Logo from '@/Components/Alo45Logo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { route } from '../../../vendor/tightenco/ziggy';

const page = usePage();
const showingMobile = ref(false);
const scrolled = ref(false);

// ── Dark Mode ─────────────────────────────────────────────
const isDark = ref(true);

// ── Toast global ──────────────────────────────────────────
const flashSuccess = computed(() => page.props.flash?.success);
const flashWarning = computed(() => page.props.flash?.warning);

const toastMessage = ref('');
const toastType = ref('success'); // 'success' or 'warning'
const showToast = ref(false);
let toastTimer = null;

const triggerToast = (msg, type) => {
    showToast.value = false;
    setTimeout(() => {
        toastMessage.value = msg;
        toastType.value = type;
        showToast.value = true;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { showToast.value = false; }, 4500);
    }, 50);
};

watch(flashSuccess, (msg) => {
    if (msg) {
        triggerToast(msg, 'success');
        page.props.flash.success = null;
    }
}, { immediate: true });

watch(flashWarning, (msg) => {
    if (msg) {
        triggerToast(msg, 'warning');
        page.props.flash.warning = null;
    }
}, { immediate: true });

const closeToast = () => {
    showToast.value = false;
    clearTimeout(toastTimer);
};

const navItems = [
    { name: 'Panel',        route: 'dashboard',        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Supervisión',  route: 'supervision.index', icon: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' },
    { name: 'Unidades',     route: 'unidades.index',    icon: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0' },
    { name: 'Conductores',  route: 'conductores.index', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { name: 'Caja',         route: 'caja.index',        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'Sanciones',    route: 'sanciones.index',   icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { name: 'Operativos',   route: 'operativos.index',  icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
    { name: 'Lista Negra',  route: 'blacklist.index',   icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' },
    { name: 'Importar',     route: 'importar.index',    icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' },
    { name: 'Supervisores', route: 'supervisores.index', icon: 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
];

const isActive = (routeName) => route().current(routeName);

// ── Alarmas & Notificaciones ──────────────────────────────
const showAlarmsDropdown = ref(false);
const soundPlayed = ref(false);
const alarmsDropdownRef = ref(null);
const bellButtonRef = ref(null);

const closeDropdownsOnClickOutside = (event) => {
    if (showAlarmsDropdown.value) {
        const clickedInsideDropdown = alarmsDropdownRef.value && alarmsDropdownRef.value.contains(event.target);
        const clickedInsideButton = bellButtonRef.value && bellButtonRef.value.contains(event.target);
        if (!clickedInsideDropdown && !clickedInsideButton) {
            showAlarmsDropdown.value = false;
        }
    }
};

const playAlarmSound = () => {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const playBeep = (freq, time, duration) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, time);
            gain.gain.setValueAtTime(0, time);
            gain.gain.linearRampToValueAtTime(0.25, time + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.001, time + duration);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(time);
            osc.stop(time + duration);
        };
        // Premium double chime
        playBeep(523.25, ctx.currentTime, 0.25); // C5
        playBeep(659.25, ctx.currentTime + 0.12, 0.4); // E5
    } catch (e) {
        console.error("AudioContext failed: ", e);
    }
};

const alarmsCount = computed(() => page.props.alarms?.total || 0);

watch(alarmsCount, (newVal, oldVal) => {
    if (newVal > (oldVal || 0)) {
        playAlarmSound();
    }
});

const handleScroll = () => { scrolled.value = window.scrollY > 10; };
onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('click', closeDropdownsOnClickOutside);
    // Force dark mode
    isDark.value = true;
    document.documentElement.classList.add('dark');
    localStorage.setItem('alo45-theme', 'dark');
    // Check alarms on mount
    if (alarmsCount.value > 0 && !soundPlayed.value) {
        setTimeout(() => {
            playAlarmSound();
            soundPlayed.value = true;
        }, 1200);
    }
});
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('click', closeDropdownsOnClickOutside);
});
</script>

<template>
    <div class="alo-page-bg min-h-screen flex flex-col relative overflow-x-hidden">
        <!-- Decorative glowing celestial green/blue blobs in the background -->
        <div class="alo-blob-1 absolute -left-20 top-20 h-96 w-96 rounded-full bg-emerald-400/20 blur-3xl pointer-events-none"></div>
        <div class="alo-blob-2 absolute -right-20 bottom-10 h-[500px] w-[500px] rounded-full bg-blue-400/15 blur-3xl pointer-events-none"></div>
        <div class="alo-blob-3 absolute left-1/3 top-1/2 h-80 w-80 rounded-full bg-sky-300/10 blur-3xl pointer-events-none"></div>



        <!-- Top bar -->
        <div class="alo-topbar bg-alo-black py-2 text-center text-xs text-white relative z-10">
            <span class="font-bold text-alo-green">📞 (054) 454545</span>
            <span class="mx-3 text-white/30">|</span>
            <a href="mailto:info@taxialo45.com.pe"
               class="text-white/60 transition-colors duration-200 hover:text-alo-green">
                info@taxialo45.com.pe
            </a>
        </div>

        <!-- Navbar -->
        <nav class="alo-navbar sticky top-0 z-50 transition-all duration-300 !overflow-visible"
             :class="[scrolled
                ? 'bg-white/95 backdrop-blur-md shadow-lg border-b border-gray-100 alo-navbar-scrolled'
                : 'bg-white border-b border-gray-100 shadow-sm']">
            <div class="mx-auto max-w-full px-4 sm:px-8 lg:px-12">
                <div class="flex h-22 items-center justify-between py-4">

                    <!-- Logo + nombre -->
                    <Link :href="route('dashboard')" class="group flex items-center gap-3.5">
                        <div class="relative">
                            <div class="absolute inset-0 rounded-full bg-alo-green-dark/25 blur-md opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                            <Alo45Logo size="nav" :show-subtitle="false" class="relative" />
                        </div>
                        <div class="hidden border-l-2 border-gray-200 pl-3.5 sm:block">
                            <p class="alo-brand-name text-lg font-black text-alo-black leading-tight">Taxi ALÓ45</p>
                            <p class="alo-brand-subtitle text-xs font-semibold uppercase tracking-wider text-alo-green-dark">
                                Sistema de Supervisión
                            </p>
                        </div>
                    </Link>

                    <!-- Nav links desktop -->
                    <div class="hidden md:flex md:items-center md:gap-3 lg:gap-5">
                        <Link
                            v-for="item in navItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="group relative flex items-center gap-2 rounded-xl px-5 py-3 text-base font-black tracking-wide transition-all duration-200"
                            :class="isActive(item.route)
                                ? 'bg-alo-green-dark text-white shadow-md shadow-alo-green-dark/30 alo-nav-link-active'
                                : 'text-gray-700 hover:bg-gray-100 hover:text-alo-green-dark alo-nav-link'"
                        >
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                            </svg>
                            {{ item.name }}
                            <!-- Indicador activo -->
                            <span v-if="isActive(item.route)"
                                  class="absolute -bottom-1.5 left-1/2 h-0.5 w-5 -translate-x-1/2 rounded-full bg-white/60"></span>
                        </Link>
                    </div>

                    <!-- Usuario + tema + logout -->
                    <div class="flex items-center gap-3">
                        <!-- Alarms Toggle & Dropdown -->
                        <div class="relative shrink-0">
                            <button
                                ref="bellButtonRef"
                                @click="showAlarmsDropdown = !showAlarmsDropdown"
                                class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-700"
                                :class="alarmsCount > 0 ? 'animate-pulse' : ''"
                                title="Alarmas del Sistema"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span v-if="alarmsCount > 0" class="absolute -top-1 -right-1 flex h-5 w-5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-black text-white ring-2 ring-white">
                                        {{ alarmsCount }}
                                    </span>
                                </span>
                            </button>

                            <!-- Dropdown Menu Rediseñado Premium (YouTube Style) Compacto -->
                            <div 
                                ref="alarmsDropdownRef"
                                v-show="showAlarmsDropdown" 
                                class="absolute top-full mt-2 w-[310px] sm:w-[320px] rounded-2xl bg-pure-white dark:bg-neutral-900 border border-gray-200/60 dark:border-neutral-800 shadow-[0_10px_35px_rgba(0,0,0,0.06)] dark:shadow-[0_15px_40px_rgba(0,0,0,0.45)] z-[100] text-gray-900 dark:text-neutral-50 right-0"
                                style="position: absolute !important; overflow: visible !important;"
                            >
                                <!-- Triángulo indicador que conecta con la campana -->
                                <div 
                                    class="absolute w-3 h-3 bg-pure-white dark:bg-neutral-900 rotate-45 border-l border-t border-gray-200/60 dark:border-neutral-800 z-0 -top-[6px] right-[14px]"
                                    style="position: absolute !important;"
                                ></div>
                                
                                <div class="relative z-10 p-3">
                                    <div class="mb-2 flex items-center justify-between border-b border-gray-100 dark:border-neutral-800 pb-2">
                                        <h3 class="font-black text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                            🔔 Notificaciones
                                            <span class="rounded-full bg-red-500 text-white text-[9px] font-black px-1.5 py-0.2" v-if="alarmsCount > 0">{{ alarmsCount }}</span>
                                        </h3>
                                        <button @click="showAlarmsDropdown = false" class="text-[10px] text-gray-400 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-white transition">Cerrar</button>
                                    </div>

                                    <!-- Contenedor scrollable para las notificaciones (estilo YouTube) -->
                                    <div class="max-h-[295px] overflow-y-auto pr-1">
                                        <div v-if="alarmsCount === 0" class="py-8 text-center flex flex-col items-center justify-center gap-1.5">
                                            <div class="h-10 w-10 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center text-xl text-emerald-500 dark:text-emerald-400">✨</div>
                                            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1">¡Todo bajo control!</p>
                                            <p class="text-[10px] text-gray-500 dark:text-neutral-400 px-2 text-center leading-normal">No hay retornos de faltas o citaciones para hoy.</p>
                                        </div>

                                        <div v-else class="divide-y divide-gray-100 dark:divide-neutral-800">
                                            <!-- ── Faltas Programadas (Retornos Obligatorios) ── -->
                                            <div v-if="page.props.alarms.faltas.length > 0">
                                                <div v-for="falta in page.props.alarms.faltas" :key="falta.id" 
                                                     class="flex items-start justify-between gap-2.5 py-2.5 hover:bg-gray-50 dark:hover:bg-neutral-800/50 transition duration-150 px-1.5 rounded-xl">
                                                    <Link
                                                        :href="route('supervision.index', { search: falta.unidad_codigo })"
                                                        class="flex-1 flex items-start gap-2.5 min-w-0"
                                                        @click="showAlarmsDropdown = false"
                                                    >
                                                        <!-- Icon Badge -->
                                                        <div class="h-8 w-8 rounded-full bg-red-500/10 text-red-500 border border-red-500/10 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/20 flex items-center justify-center text-sm shrink-0 shadow-sm">
                                                            🚨
                                                        </div>
                                                        <!-- Text Content -->
                                                        <div class="flex-1 min-w-0 text-left">
                                                            <p class="text-[11px] text-gray-900 dark:text-neutral-50 leading-snug">
                                                                <span class="font-black text-red-600 dark:text-red-400">Retorno Unidad #{{ falta.unidad_codigo }}</span> - Chofer <span class="font-bold text-gray-800 dark:text-neutral-200">{{ falta.conductor_nombre }}</span> debe reportarse hoy.
                                                            </p>
                                                            <p class="text-[10px] text-gray-500 dark:text-neutral-400 mt-0.5 italic line-clamp-2 leading-relaxed">
                                                                "{{ falta.descripcion }}"
                                                            </p>
                                                            <p class="text-[8px] text-gray-400 dark:text-neutral-500 mt-1 font-mono">
                                                                Fecha: {{ falta.fecha_alarma }}
                                                            </p>
                                                        </div>
                                                    </Link>
                                                    <!-- Close Action -->
                                                    <Link
                                                        :href="route('supervision.faltas.descartar-alarma', falta.id)"
                                                        method="post"
                                                        as="button"
                                                        class="text-gray-400 hover:text-red-500 dark:text-neutral-500 dark:hover:text-red-400 font-bold shrink-0 text-sm p-1 transition self-start"
                                                        title="Marcar como atendido / Descartar"
                                                    >✕</Link>
                                                </div>
                                            </div>

                                            <!-- ── Citados Hoy ── -->
                                            <div v-if="page.props.alarms.citados.length > 0">
                                                <div v-for="citado in page.props.alarms.citados" :key="citado.id" 
                                                     class="flex items-start justify-between gap-2.5 py-2.5 hover:bg-gray-50 dark:hover:bg-neutral-800/50 transition duration-150 px-1.5 rounded-xl">
                                                    <Link
                                                        :href="route('conductores.index', { search: citado.unidad_codigo || citado.nombre })"
                                                        class="flex-1 flex items-start gap-2.5 min-w-0"
                                                        @click="showAlarmsDropdown = false"
                                                    >
                                                        <!-- Icon Badge -->
                                                        <div class="h-8 w-8 rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/10 dark:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/20 flex items-center justify-center text-sm shrink-0 shadow-sm">
                                                            📅
                                                        </div>
                                                        <!-- Text Content -->
                                                        <div class="flex-1 min-w-0 text-left">
                                                            <p class="text-[11px] text-gray-900 dark:text-neutral-50 leading-snug">
                                                                <span class="font-black text-amber-600 dark:text-amber-400">Citación de Chofer</span> - El conductor <span class="font-bold text-gray-800 dark:text-neutral-200">{{ citado.nombre }}</span> (Unidad #{{ citado.unidad_codigo || '—' }}) está citado para hoy.
                                                            </p>
                                                            <p class="text-[10px] text-gray-500 dark:text-neutral-400 mt-0.5 italic line-clamp-2 leading-relaxed" v-if="citado.motivo">
                                                                "Motivo: {{ citado.motivo }}"
                                                            </p>
                                                            <p class="text-[8px] text-gray-400 dark:text-neutral-500 mt-1 font-mono">
                                                                Citación: Hoy
                                                            </p>
                                                        </div>
                                                    </Link>
                                                    <!-- Marcar como atendido / Quitar citación -->
                                                    <Link
                                                        :href="route('conductores.quitar-citacion', citado.id)"
                                                        method="put"
                                                        as="button"
                                                        class="text-gray-400 hover:text-emerald-500 dark:text-neutral-500 dark:hover:text-[#00ff66] font-bold shrink-0 text-sm p-1 transition self-start"
                                                        title="Marcar como atendido / Quitar citación"
                                                        @click="showAlarmsDropdown = false"
                                                    >✓</Link>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="alo-user-pill hidden items-center gap-2 rounded-full bg-gray-100 px-4 py-2 sm:flex">
                            <div class="h-7 w-7 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center">
                                <img v-if="page.props.auth.user.avatar" :src="'/' + page.props.auth.user.avatar" class="h-full w-full object-cover" />
                                <div v-else class="alo-user-avatar flex h-full w-full items-center justify-center rounded-full bg-alo-green-dark text-xs font-black text-white">
                                    {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <span class="text-sm font-bold text-gray-700">{{ page.props.auth.user.name }}</span>
                        </div>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="alo-logout-btn flex items-center gap-2 rounded-xl border-2 border-alo-red/70 px-4 py-2 text-sm font-bold text-alo-red transition-all duration-200 hover:bg-alo-red hover:text-white hover:shadow-md hover:shadow-alo-red/20"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Salir
                        </Link>

                        <!-- Hamburguesa móvil -->
                        <button
                            class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 md:hidden"
                            @click="showingMobile = !showingMobile"
                        >
                            <svg class="h-5 w-5 transition-transform duration-200"
                                 :class="showingMobile ? 'rotate-90' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path v-if="!showingMobile" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-show="showingMobile" class="border-t border-gray-100 pb-3 pt-2 md:hidden">
                        <Link
                            v-for="item in navItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold transition"
                            :class="isActive(item.route)
                                ? 'bg-alo-green-dark text-white'
                                : 'text-gray-600 hover:bg-gray-100'"
                            @click="showingMobile = false"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                            </svg>
                            {{ item.name }}
                        </Link>
                    </div>
                </Transition>
            </div>
        </nav>

        <!-- Hero strip premium -->
        <div class="hero-strip py-8 text-white">
            <div class="relative z-10 mx-auto max-w-full px-4 sm:px-8 lg:px-12">
                <div class="animate-fade-down">
                    <slot name="header" />
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <main class="flex-grow mx-auto max-w-full px-4 py-8 sm:px-8 lg:px-12">
            <div class="animate-fade-up">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="alo-footer mt-12 bg-alo-green-dark border-t border-emerald-800 text-white">
            <div class="mx-auto max-w-full px-4 py-8 sm:px-8 lg:px-12">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between">
                    <div class="flex items-center gap-3">
                        <Alo45Logo size="sm" :show-subtitle="false" class="opacity-90" />
                        <div>
                            <p class="text-sm font-black text-white">Taxi ALÓ45</p>
                            <p class="text-xs text-emerald-200/80">Sistema de Supervisión Interna</p>
                        </div>
                    </div>
                    <div class="text-center sm:text-right text-emerald-100/70">
                        <p class="text-xs">© {{ new Date().getFullYear() }} taxialo45.com.pe</p>
                        <p class="text-xs text-emerald-300 font-semibold">Vive la experiencia ALÓ45 — Arequipa</p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- ── Toast global de éxito / warning ── -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-full scale-95"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-12 scale-95"
        >
            <div v-if="showToast && toastMessage"
                class="fixed bottom-6 right-6 z-[200] flex w-full max-w-[340px] items-start gap-3 rounded-2xl bg-white/95 dark:bg-gray-950/95 backdrop-blur-md px-5 py-4 shadow-[0_8px_32px_rgba(0,0,0,0.15)] border border-gray-100 dark:border-white/5 overflow-hidden select-none"
            >
                <!-- Barra lateral izquierda -->
                <span class="absolute left-0 inset-y-0 w-1 rounded-l-2xl"
                    :class="toastType === 'success' ? 'bg-alo-green' : 'bg-alo-red'"
                ></span>

                <!-- Icono -->
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                    :class="toastType === 'success' ? 'bg-alo-green/10' : 'bg-alo-red/10'"
                >
                    <svg v-if="toastType === 'success'" class="h-5 w-5 text-alo-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <!-- Icono X para revertir/error -->
                    <svg v-else class="h-5 w-5 text-alo-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <!-- Texto -->
                <div class="flex-1 min-w-0 pl-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest"
                        :class="toastType === 'success' ? 'text-alo-green' : 'text-alo-red'"
                    >
                        {{ toastType === 'success' ? 'Operación exitosa' : 'Operación revertida' }}
                    </p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-200 leading-snug">{{ toastMessage }}</p>
                </div>

                <!-- Cerrar -->
                <button @click="closeToast"
                    class="shrink-0 rounded-lg p-1 text-gray-400 dark:text-gray-500 transition hover:bg-gray-100 dark:hover:bg-white/10 hover:text-gray-600 dark:hover:text-white"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Barra de progreso -->
                <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-gray-100 dark:bg-white/10">
                    <div class="h-full toast-progress-bar"
                        :class="toastType === 'success' ? 'bg-alo-green' : 'bg-alo-red'"
                    ></div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes toastProgress {
    from { width: 100%; }
    to { width: 0%; }
}
.toast-progress-bar {
    animation: toastProgress 4.5s linear forwards;
}
</style>
