<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Alo45Logo from '@/Components/Alo45Logo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
    },
    phpVersion: {
        type: String,
    },
});
</script>

<template>
    <Head title="Bienvenido — Taxi ALÓ45" />

    <div class="relative min-h-screen overflow-hidden bg-alo-black text-white">
        <!-- Fondo animado con gradiente corporativo -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#101010] via-[#0b2413] to-[#00170a]"></div>

        <!-- Círculos decorativos de luz difuminada -->
        <div class="absolute -top-40 -left-40 h-[500px] w-[500px] rounded-full bg-alo-green/10 blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -right-40 h-[500px] w-[500px] rounded-full bg-alo-green/5 blur-3xl animate-float delay-500"></div>
        <div class="absolute top-1/3 right-1/4 h-80 w-80 rounded-full bg-alo-red/5 blur-3xl"></div>

        <!-- Patrón de puntos sutil en el fondo -->
        <div class="absolute inset-0 opacity-5"
             style="background-image: radial-gradient(circle, #00983F 1px, transparent 1px); background-size: 32px 32px;"></div>

        <!-- Navbar superior -->
        <header class="relative z-10 border-b border-white/5 bg-white/5 py-4 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-center px-6 relative min-h-[56px]">
                <!-- Logotipo e Identidad - Centrado -->
                <div class="flex items-center gap-3 select-none">
                    <Alo45Logo size="sm" :show-subtitle="false" class="drop-shadow-lg" />
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-black tracking-wide">
                            Taxi <span class="text-alo-green">ALÓ</span><span class="text-alo-red">45</span>
                        </span>
                        <span class="border-l border-white/20 pl-3 ml-1 text-xs font-semibold uppercase tracking-widest text-white/40">
                            Supervisión
                        </span>
                    </div>
                </div>

                <!-- Enlaces de navegación - A la derecha -->
                <nav v-if="canLogin" class="absolute right-6">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-xl bg-alo-green px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-alo-green/20 transition hover:bg-alo-green-dark"
                    >
                        Panel de Control →
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="rounded-xl border border-white/20 bg-white/5 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-white/10"
                    >
                        Iniciar Sesión
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Contenido Hero Principal -->
        <main class="relative z-10 mx-auto flex max-w-7xl flex-col items-center justify-center px-6 py-16 text-center lg:py-24">
            <!-- Píldora decorativa -->
            <div class="mb-6 animate-fade-down inline-flex items-center gap-2 rounded-full border border-alo-green/30 bg-alo-green/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-alo-green">
                <span class="flex h-2 w-2 rounded-full bg-alo-green pulse-ring"></span>
                Plataforma de Control Interno
            </div>

            <!-- Título Principal -->
            <h1 class="max-w-4xl text-4xl font-black tracking-tight text-white sm:text-6xl animate-fade-up">
                Control y Supervisión de Flota <br />
                <span class="text-gradient-green">en Tiempo Real</span>
            </h1>

            <p class="mt-6 max-w-2xl text-base text-white/60 sm:text-lg animate-fade-up delay-100">
                Optimiza la gestión interna de unidades, conductores y registros de supervisión. 
                Garantiza la excelencia del servicio de Taxi ALÓ45 con nuestra plataforma centralizada de monitoreo.
            </p>

            <!-- Acciones CTA -->
            <div class="mt-10 flex flex-wrap justify-center gap-4 animate-fade-up delay-200">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="btn-shimmer rounded-xl px-8 py-4 text-sm font-bold uppercase tracking-wider text-white shadow-xl"
                >
                    Ir al Panel Administrativo
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="btn-shimmer rounded-xl px-8 py-4 text-sm font-bold uppercase tracking-wider text-white shadow-xl"
                >
                    Ingresar al Sistema
                </Link>
            </div>

            <!-- Tarjetas de Módulos (Características del Sistema) -->
            <div class="mt-20 grid w-full gap-6 sm:grid-cols-2 lg:grid-cols-4 animate-fade-up delay-300">
                <!-- Tarjeta 1: Supervisión -->
                <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 p-6 text-left backdrop-blur-xl transition duration-300 hover:border-alo-green/20 hover:bg-white/8 hover:-translate-y-1">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-alo-green/10 text-alo-green group-hover:scale-110 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">Supervisión</h3>
                    <p class="mt-2 text-sm text-white/55">
                        Registro ágil de observaciones y faltas clasificados por rutas (San Pablo, Cerro Verde, etc.).
                    </p>
                </div>

                <!-- Tarjeta 2: Flota -->
                <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 p-6 text-left backdrop-blur-xl transition duration-300 hover:border-alo-green/20 hover:bg-white/8 hover:-translate-y-1">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-alo-green/10 text-alo-green group-hover:scale-110 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">Control de Flota</h3>
                    <p class="mt-2 text-sm text-white/55">
                        Monitoreo del estado operativo de las unidades en tiempo real (activo, inactivo, mantenimiento).
                    </p>
                </div>

                <!-- Tarjeta 3: Conductores -->
                <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 p-6 text-left backdrop-blur-xl transition duration-300 hover:border-alo-green/20 hover:bg-white/8 hover:-translate-y-1">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-alo-green/10 text-alo-green group-hover:scale-110 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">Recursos Humanos</h3>
                    <p class="mt-2 text-sm text-white/55">
                        Base de datos unificada de conductores, DNI, licencias y vinculación directa con sus unidades.
                    </p>
                </div>

                <!-- Tarjeta 4: Importador -->
                <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-white/5 p-6 text-left backdrop-blur-xl transition duration-300 hover:border-alo-green/20 hover:bg-white/8 hover:-translate-y-1">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-alo-green/10 text-alo-green group-hover:scale-110 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">Importador Inteligente</h3>
                    <p class="mt-2 text-sm text-white/55">
                        Carga y mapeo automático de plantillas Excel para la actualización rápida de conductores y vehículos.
                    </p>
                </div>
            </div>
        </main>

        <!-- Pie de página (Footer) -->
        <footer class="relative z-10 border-t border-white/5 bg-white/2 py-8 text-center text-xs text-white/30">
            <div class="mx-auto max-w-7xl px-6 flex items-center justify-center">
                <p>© {{ new Date().getFullYear() }} taxialo45.com.pe · Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>
</template>
