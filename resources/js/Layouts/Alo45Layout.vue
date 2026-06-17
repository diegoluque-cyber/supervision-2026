<script setup>
import Alo45Logo from '@/Components/Alo45Logo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { route } from '../../../vendor/tightenco/ziggy';

const page = usePage();
const showingMobile = ref(false);
const scrolled = ref(false);

// ── Dark Mode ─────────────────────────────────────────────
const isDark = ref(false);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('alo45-theme', isDark.value ? 'dark' : 'light');
    // Add transition class temporarily for smooth switch
    document.documentElement.classList.add('theme-transition');
    setTimeout(() => document.documentElement.classList.remove('theme-transition'), 500);
};

// ── Toast global ──────────────────────────────────────────
const flashSuccess = computed(() => page.props.flash?.success);
const showToast = ref(false);
let toastTimer = null;

watch(flashSuccess, (msg) => {
    if (msg) {
        showToast.value = true;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { showToast.value = false; }, 4500);
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
    { name: 'Lista Negra',  route: 'blacklist.index',   icon: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' },
    { name: 'Importar',     route: 'importar.index',    icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12' },
    { name: 'Supervisores', route: 'supervisores.index', icon: 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
];

const isActive = (routeName) => route().current(routeName);

const handleScroll = () => { scrolled.value = window.scrollY > 10; };
onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    // Restore saved theme
    const saved = localStorage.getItem('alo45-theme');
    if (saved === 'dark') {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    }
});
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <div class="alo-page-bg min-h-screen flex flex-col relative overflow-hidden" style="background: linear-gradient(to bottom, #d1fae5 0%, #ecfdf5 20%, #e0f2fe 65%, #eff6ff 100%)">
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
        <nav class="alo-navbar sticky top-0 z-50 transition-all duration-300"
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
                        <!-- Theme Toggle -->
                        <button
                            @click="toggleTheme"
                            class="theme-toggle"
                            :title="isDark ? 'Modo Claro' : 'Modo Oscuro'"
                        >
                            <!-- Sol (visible en dark mode) -->
                            <svg v-if="isDark" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <!-- Luna (visible en light mode) -->
                            <svg v-else class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </button>

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

        <!-- ── Toast global de éxito ── -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-full scale-95"
            enter-to-class="opacity-100 translate-x-0 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-x-0 scale-100"
            leave-to-class="opacity-0 translate-x-12 scale-95"
        >
            <div v-if="showToast && flashSuccess"
                class="fixed top-[76px] right-5 z-[200] flex w-full max-w-[340px] items-start gap-3 rounded-2xl bg-white px-5 py-4 shadow-[0_8px_32px_rgba(0,0,0,0.14)] ring-1 ring-gray-100 overflow-hidden"
            >
                <!-- Barra lateral izquierda -->
                <span class="absolute left-0 inset-y-0 w-1 rounded-l-2xl bg-alo-green"></span>

                <!-- Icono -->
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-alo-green/10">
                    <svg class="h-5 w-5 text-alo-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <!-- Texto -->
                <div class="flex-1 min-w-0 pl-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-alo-green">Operación exitosa</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800 leading-snug">{{ flashSuccess }}</p>
                </div>

                <!-- Cerrar -->
                <button @click="closeToast"
                    class="shrink-0 rounded-lg p-1 text-gray-300 transition hover:bg-gray-100 hover:text-gray-500"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Barra de progreso -->
                <div class="absolute bottom-0 left-0 right-0 h-[3px] bg-gray-100">
                    <div class="h-full bg-alo-green toast-progress-bar"></div>
                </div>
            </div>
        </Transition>
    </div>
</template>
