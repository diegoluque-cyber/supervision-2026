<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const page = usePage();
const successMsg = computed(() => page.props.flash?.success);

const props = defineProps({
    supervisores: Array,
    stats: Object,
});

const showForm = ref(false);
const editing = ref(null);
const avatarPreview = ref(null);

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    avatar: null,
});

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.avatar = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            avatarPreview.value = event.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const openCreate = () => {
    editing.value = null;
    avatarPreview.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (s) => {
    editing.value = s.id;
    avatarPreview.value = s.avatar ? '/' + s.avatar : null;
    form.reset();
    form.clearErrors();
    form.name = s.name;
    form.username = s.username;
    form.email = s.email;
    form.avatar = null;
    showForm.value = true;
};

const submit = () => {
    if (editing.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('supervisores.update', editing.value), {
            onSuccess: () => { showForm.value = false; form.reset(); avatarPreview.value = null; },
        });
    } else {
        form.post(route('supervisores.store'), {
            onSuccess: () => { showForm.value = false; form.reset(); avatarPreview.value = null; },
        });
    }
};

// Eliminar individual
const confirmDelete = ref(null);
const doDelete = () => {
    router.delete(route('supervisores.destroy', confirmDelete.value), {
        onSuccess: () => { confirmDelete.value = null; },
    });
};

// Bulk delete
const selectedIds = ref([]);
const confirmBulkDelete = ref(false);

const allSelected = computed({
    get: () => selectedIds.value.length === props.supervisores.length && props.supervisores.length > 0,
    set: (val) => { selectedIds.value = val ? props.supervisores.map(s => s.id) : []; },
});

const toggleSelect = (id) => {
    const idx = selectedIds.value.indexOf(id);
    if (idx === -1) selectedIds.value.push(id);
    else selectedIds.value.splice(idx, 1);
};

const doBulkDelete = () => {
    router.post(route('supervisores.destroyMultiple'), {
        ids: selectedIds.value,
    }, {
        onSuccess: () => {
            confirmBulkDelete.value = false;
            selectedIds.value = [];
        },
    });
};

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
</script>

<template>
    <Alo45Layout>
        <Head title="Supervisores — Taxi ALÓ45" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <!-- Left Title -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">Administración</p>
                    <h1 class="mt-1 text-2xl font-black text-white">Gestión de Supervisores</h1>
                    <p class="mt-1 text-sm text-gray-300/80">
                        <span class="font-bold text-white">{{ stats.total }}</span> supervisores registrados
                    </p>
                </div>

                <!-- Center: Reloj y Lema Corporativo -->
                <div class="flex flex-col items-center justify-center bg-white/5 border border-white/10 rounded-2xl px-6 py-2 backdrop-blur-xl max-w-sm mx-auto lg:mx-0">
                    <p class="text-xs font-bold text-emerald-300 uppercase tracking-widest text-center select-none">{{ greetingMessage }}</p>
                    <p class="text-2xl font-black text-white tracking-widest mt-1 font-mono select-none">{{ timeString }}</p>
                </div>

                <!-- Right: Botón Registro Premium -->
                <button
                    @click="openCreate"
                    class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-emerald-500/25 flex items-center justify-center gap-2"
                >
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    <span class="relative flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo supervisor
                    </span>
                </button>
            </div>
        </template>

        <div v-if="successMsg" class="mb-4 flex items-center justify-center gap-2.5 rounded-xl border border-alo-green bg-green-50 px-5 py-3.5 text-sm font-semibold text-alo-green shadow-sm">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ successMsg }}
        </div>

        <div class="mb-6 grid grid-cols-3 gap-3">
            <div class="rounded-xl bg-white p-3 text-center shadow-card">
                <div class="mx-auto mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-alo-green/10 dark:bg-emerald-500/10">
                    <svg class="h-4 w-4 text-alo-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-alo-green">{{ stats.total }}</p>
                <p class="text-[10px] font-semibold text-gray-500">Supervisores</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-card">
                <div class="mx-auto mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/10">
                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-blue-600 dark:text-blue-400">{{ supervisores.length }}</p>
                <p class="text-[10px] font-semibold text-gray-500">Activos</p>
            </div>
            <div class="rounded-xl bg-white p-3 text-center shadow-card">
                <div class="mx-auto mb-1.5 flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800/60">
                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <p class="text-lg font-black text-gray-500 dark:text-gray-300">{{ stats.total - supervisores.length }}</p>
                <p class="text-[10px] font-semibold text-gray-500">Bloqueados</p>
            </div>
        </div>


        <!-- Bulk toolbar -->
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

        <div class="overflow-hidden rounded-xl bg-white shadow-card">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-alo-green">
                        <tr>
                            <th class="w-10 px-4 py-3 text-left">
                                <input type="checkbox" v-model="allSelected"
                                    class="h-4 w-4 rounded border-white/40 bg-white/20 text-alo-green focus:ring-alo-green dark:border-emerald-500/40 dark:bg-black/40 dark:focus:ring-emerald-500/30" />
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-white">Creado</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase text-white">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="s in supervisores" :key="s.id" class="transition hover:bg-green-50/50" :class="selectedIds.includes(s.id) ? 'bg-alo-green/5' : ''">
                            <td class="w-10 whitespace-nowrap px-4 py-3 text-sm">
                                <input type="checkbox" :checked="selectedIds.includes(s.id)" @click="toggleSelect(s.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-alo-green focus:ring-alo-green dark:bg-black/60 dark:border-white/20 dark:checked:bg-[#00ff66] dark:checked:border-[#00ff66] dark:focus:ring-[#00ff66]/30" />
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-alo-black">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center dark:border-gray-700 dark:bg-gray-800">
                                        <img v-if="s.avatar" :src="'/' + s.avatar" class="h-full w-full object-cover" />
                                        <div v-else class="h-full w-full flex items-center justify-center bg-alo-green-dark/15 text-xs font-bold text-alo-green-dark dark:bg-emerald-500/10 dark:text-[#00ff66]">
                                            {{ s.name.charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <span class="dark:text-white">{{ s.name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-alo-gray-dark">{{ s.username }}</td>
                            <td class="px-4 py-3 text-sm text-alo-gray-dark">{{ s.email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-400">{{ s.created_at }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit(s)"
                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-blue-600 transition hover:bg-blue-50">
                                        Editar
                                    </button>
                                    <button @click="confirmDelete = s.id"
                                        class="rounded-lg px-2.5 py-1 text-xs font-semibold text-alo-red transition hover:bg-red-50">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="supervisores.length === 0" class="py-16 text-center text-sm text-gray-400">
                No hay supervisores registrados.
            </div>
        </div>

        <!-- Modal crear/editar -->
        <Teleport to="body">
            <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 class="font-bold text-alo-black dark:text-white">{{ editing ? 'Editar' : 'Nuevo' }} Supervisor</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-300">{{ editing ? 'Actualiza los datos del supervisor' : 'Crea un nuevo acceso al sistema' }}</p>
                    </div>
                    <button @click="showForm = false; form.reset()" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="space-y-4 px-6 py-5">
                    <!-- Foto de Perfil / Avatar Upload -->
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-full border border-gray-200 bg-gray-100 flex items-center justify-center relative shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <img v-if="avatarPreview" :src="avatarPreview" class="h-full w-full object-cover" />
                            <span v-else class="text-2xl font-bold text-gray-300 dark:text-gray-600">👤</span>
                        </div>
                        <div class="flex-1">
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Foto de Perfil</label>
                            <input type="file" accept="image/*" @change="handleAvatarChange"
                                class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 dark:file:bg-emerald-900/40 file:text-alo-green dark:file:text-emerald-400 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60" />
                        </div>
                    </div>
                    <p v-if="form.errors.avatar" class="mt-1 text-xs text-red-500">{{ form.errors.avatar }}</p>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Nombre completo *</label>
                        <input v-model="form.name" type="text" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Usuario *</label>
                            <input v-model="form.username" type="text" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20" />
                            <p v-if="form.errors.username" class="mt-1 text-xs text-red-500">{{ form.errors.username }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Email *</label>
                            <input v-model="form.email" type="email" required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Contraseña {{ editing ? '(dejar vacío para mantener)' : '*' }}</label>
                            <input v-model="form.password" type="password" :required="!editing" minlength="8"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20" />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-alo-black dark:text-white">Confirmar contraseña {{ editing ? '' : '*' }}</label>
                            <input v-model="form.password_confirmation" type="password" :required="!editing"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-alo-green focus:outline-none focus:ring-2 focus:ring-alo-green/20" />
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showForm = false; form.reset()"
                            class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="flex-1 rounded-lg bg-alo-green py-2 text-sm font-bold text-white hover:bg-alo-green-dark disabled:opacity-50">
                            {{ form.processing ? 'Guardando...' : (editing ? 'Actualizar' : 'Crear supervisor') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </Teleport>

        <!-- Modal confirmar eliminar -->
        <Teleport to="body">
            <div v-if="confirmDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-alo-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h2 class="mb-1 font-bold text-alo-black dark:text-white">¿Eliminar supervisor?</h2>
                <p class="mb-6 text-sm text-alo-gray-dark dark:text-gray-300">Perderá el acceso al sistema. Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <button @click="confirmDelete = null"
                        class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                        Cancelar
                    </button>
                    <button @click="doDelete"
                        class="flex-1 rounded-lg bg-alo-red py-2 text-sm font-bold text-white hover:bg-alo-red-dark">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- Modal bulk delete -->
        <Teleport to="body">
            <div v-if="confirmBulkDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-alo-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h2 class="mb-1 font-bold text-alo-black">¿Eliminar {{ selectedIds.length }} supervisores?</h2>
                <p class="mb-6 text-sm text-alo-gray-dark">Perderán el acceso al sistema. Esta acción no se puede deshacer.</p>
                <div class="flex gap-3">
                    <button @click="confirmBulkDelete = false"
                        class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">
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
