<script setup>
const logoUrl = '/images/logo_alo45.png';
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-alo-black">

        <!-- Fondo animado con gradiente -->
        <div class="absolute inset-0 bg-gradient-to-br from-alo-black via-[#0d2b17] to-[#001a0c]"></div>

        <!-- Círculos decorativos de fondo -->
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-alo-green/10 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-alo-green/10 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/4 h-64 w-64 rounded-full bg-alo-red/5 blur-3xl"></div>

        <!-- Patrón de puntos de fondo -->
        <div class="absolute inset-0 opacity-5"
             style="background-image: radial-gradient(circle, #00983F 1px, transparent 1px); background-size: 32px 32px;"></div>

        <!-- Barra superior -->
        <div class="relative z-10 border-b border-white/5 bg-white/5 py-2 text-center text-xs text-white backdrop-blur-sm">
            <span class="font-bold text-alo-green">📞 (054) 454545</span>
            <span class="mx-3 text-white/30">|</span>
            <a href="mailto:info@taxialo45.com.pe" class="text-white/60 transition hover:text-alo-green">info@taxialo45.com.pe</a>
        </div>

        <!-- Contenido principal -->
        <div class="relative z-10 flex min-h-[calc(100vh-36px)] items-center justify-center px-4 py-12">
            <div class="w-full max-w-sm">

                <!-- Logo grande con animación -->
                <div class="mb-8 flex flex-col items-center">
                    <div class="relative">
                        <!-- Halo verde detrás del logo -->
                        <div class="absolute inset-0 rounded-full bg-alo-green/20 blur-xl scale-110"></div>
                        <img
                            :src="logoUrl"
                            alt="Taxi ALÓ45"
                            class="relative h-32 w-32 rounded-full overflow-hidden object-cover drop-shadow-2xl"
                        />
                    </div>
                    <h1 class="mt-4 text-2xl font-black tracking-wide text-white">
                        Taxi <span class="text-alo-green">ALÓ</span><span class="text-alo-red">45</span>
                    </h1>
                    <p class="mt-1 text-xs font-medium uppercase tracking-widest text-white/40">
                        Sistema de Supervisión
                    </p>
                </div>

                <!-- Card de login -->
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/5 shadow-2xl backdrop-blur-xl">
                    <!-- Barra superior de color -->
                    <div class="h-1 bg-gradient-to-r from-alo-green via-alo-red to-alo-green"></div>

                    <div class="px-8 py-8">
                        <slot />
                    </div>
                </div>

                <!-- Footer -->
                <p class="mt-6 text-center text-xs text-white/30">
                    © {{ new Date().getFullYear() }} taxialo45.com.pe · Todos los derechos reservados
                </p>
            </div>
        </div>
    </div>
</template>
