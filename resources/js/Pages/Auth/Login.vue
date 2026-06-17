<script setup>
import Alo45GuestLayout from '@/Layouts/Alo45GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    usuario: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Alo45GuestLayout>
        <Head title="Iniciar Sesión — Taxi ALÓ45" />

        <div class="relative mb-8 text-center">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 blur-2xl rounded-full"></div>
            <div class="relative">
                <h2 class="text-lg font-bold text-white">Bienvenido de vuelta</h2>
                <p class="mt-1 text-sm text-gray-300/80">Ingresa tus credenciales para continuar</p>
            </div>
        </div>

        <div v-if="status" class="relative mb-6 overflow-hidden rounded-xl border border-emerald-500/30 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 px-4 py-3 text-sm text-emerald-400 backdrop-blur-xl">
            <div class="absolute inset-0 bg-white/5"></div>
            <div class="relative">
                {{ status }}
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6 relative">
            <!-- Usuario premium -->
            <div class="relative group">
                <label for="usuario" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-emerald-400/90">
                    Usuario
                </label>
                <div class="relative z-10">
                    <input
                        id="usuario"
                        v-model="form.usuario"
                        type="text"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Tu usuario"
                        class="login-input w-full rounded-xl border border-white/10 bg-white/5 pl-10 pr-4 py-3.5 text-sm text-white placeholder-gray-400/80 backdrop-blur-xl transition-all duration-300 focus:border-emerald-500/60 focus:bg-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                    />
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-400/80 transition-colors group-focus-within:text-emerald-500 pointer-events-none z-30">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.usuario" />
            </div>

            <!-- Contraseña premium -->
            <div class="relative group">
                <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-emerald-400/90">
                    Contraseña
                </label>
                <div class="relative z-10">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="Tu contraseña"
                        class="login-input w-full rounded-xl border border-white/10 bg-white/5 pl-10 pr-12 py-3.5 text-sm text-white placeholder-gray-400/80 backdrop-blur-xl transition-all duration-300 focus:border-emerald-500/60 focus:bg-white/10 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                    />
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-400/80 transition-colors group-focus-within:text-emerald-500 pointer-events-none z-30">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-emerald-400/80 transition-all duration-300 hover:text-emerald-500 z-30"
                    >
                        <svg v-if="!showPassword" class="h-4 w-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="h-4 w-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Recordar sesión premium -->
            <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl bg-white/5 p-3 transition-all duration-300 hover:bg-white/10">
                <div class="relative">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/20 bg-white/10 text-emerald-500 focus:ring-emerald-500/30 transition-all duration-300"
                    />
                </div>
                <span class="text-sm text-gray-300/90 transition-colors group-hover:text-white">Mantener sesión iniciada</span>
            </label>

            <!-- Botón de login premium -->
            <button
                type="submit"
                :disabled="form.processing"
                class="btn-led-green group relative w-full overflow-hidden rounded-xl py-4 text-sm font-bold uppercase tracking-widest shadow-xl transition-all duration-300 disabled:opacity-50"
            >
                <span v-if="!form.processing" class="relative flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Ingresar al sistema
                </span>
                <span v-else class="relative flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Verificando...
                </span>
            </button>
        </form>

        <!-- Info seguridad premium -->
        <div class="mt-8 flex items-center justify-center gap-2 text-xs text-gray-400/80">
            <div class="relative">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <span>Conexión segura · Acceso restringido</span>
        </div>
    </Alo45GuestLayout>
</template>
