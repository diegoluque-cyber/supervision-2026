<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from '../../../../vendor/tightenco/ziggy';

const props = defineProps({
    meeting: Object, // Detalles de la reunión
    attendances: Array, // Asistencias pre-guardadas
});

// Inicializar el formulario con los registros existentes
const form = useForm({
    asistencias: props.attendances.map(a => ({
        unidad_id: a.unidad_id,
        codigo: a.codigo,
        placa: a.placa,
        conductor: a.conductor,
        estado: a.estado, // asistio, tardanza, falta, justificado
        observacion: a.observacion || '',
    })),
});

const submitAttendance = () => {
    form.post(route('supervisores.asistencia.save', props.meeting.id));
};

const markAllAsPresent = () => {
    form.asistencias.forEach(a => {
        a.estado = 'asistio';
    });
};

const setStatus = (index, status) => {
    form.asistencias[index].estado = status;
};
</script>

<template>
    <Alo45Layout>
        <Head :title="'Pasar Asistencia: ' + meeting.fecha_label + ' — Taxi ALÓ45'" />

        <template #header>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('supervisores.asistencia.index')"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-xl bg-white/10 text-white hover:bg-white/20 transition"
                        title="Volver"
                    >
                        🔙
                    </Link>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400/90">
                            Hoja de Asistencia — {{ meeting.fecha_label }}
                        </p>
                        <h1 class="mt-1 text-2xl font-black text-white">
                            {{ meeting.tema }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0 self-start">
                    <button
                        type="button"
                        @click="markAllAsPresent"
                        class="rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 px-4 py-2 text-sm font-bold text-white transition duration-200"
                    >
                        ✔️ Marcar todos como Asistió
                    </button>
                    <button
                        type="button"
                        @click="submitAttendance"
                        :disabled="form.processing"
                        class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2 text-sm font-bold text-white shadow-lg transition duration-200 hover:scale-105"
                    >
                        💾 Guardar Asistencia
                    </button>
                </div>
            </div>
        </template>

        <!-- Listado de Asistencias -->
        <div class="bg-white/80 dark:bg-neutral-900/90 rounded-2xl border border-gray-100 dark:border-neutral-800 p-6 shadow-sm">
            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-neutral-800/80">
                <table class="w-full border-collapse text-left text-sm text-gray-700 dark:text-neutral-300">
                    <thead class="bg-gray-50/50 dark:bg-neutral-950/40 text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-400 border-b border-gray-100 dark:border-neutral-800">
                        <tr>
                            <th class="px-6 py-4">Móvil</th>
                            <th class="px-6 py-4">Conductor Supervisor</th>
                            <th class="px-6 py-4 text-center">Estado de Asistencia</th>
                            <th class="px-6 py-4">Observaciones del Día</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-800">
                        <tr v-for="(a, index) in form.asistencias" :key="a.unidad_id" class="hover:bg-emerald-500/5 transition">
                            <!-- Código Unidad -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="rounded-lg bg-emerald-500/10 dark:bg-emerald-500/20 px-3 py-1.5 text-xs font-black text-emerald-700 dark:text-emerald-400">
                                    #{{ a.codigo }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-neutral-500 block mt-1.5 font-mono">Placa: {{ a.placa }}</span>
                            </td>

                            <!-- Conductor -->
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                {{ a.conductor }}
                            </td>

                            <!-- Botones de Estado -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex rounded-xl bg-gray-100/70 dark:bg-neutral-950/60 p-1 border border-gray-200/50 dark:border-neutral-800">
                                    <button
                                        type="button"
                                        @click="setStatus(index, 'asistio')"
                                        class="rounded-lg px-3 py-1.5 text-xs font-black transition duration-200"
                                        :class="a.estado === 'asistio'
                                            ? 'bg-emerald-500 text-white shadow-sm'
                                            : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-white'"
                                    >
                                        Asistió
                                    </button>
                                    <button
                                        type="button"
                                        @click="setStatus(index, 'tardanza')"
                                        class="rounded-lg px-3 py-1.5 text-xs font-black transition duration-200"
                                        :class="a.estado === 'tardanza'
                                            ? 'bg-amber-500 text-white shadow-sm'
                                            : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-white'"
                                    >
                                        Tardanza
                                    </button>
                                    <button
                                        type="button"
                                        @click="setStatus(index, 'falta')"
                                        class="rounded-lg px-3 py-1.5 text-xs font-black transition duration-200"
                                        :class="a.estado === 'falta'
                                            ? 'bg-red-500 text-white shadow-sm'
                                            : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-white'"
                                    >
                                        Falta
                                    </button>
                                    <button
                                        type="button"
                                        @click="setStatus(index, 'justificado')"
                                        class="rounded-lg px-3 py-1.5 text-xs font-black transition duration-200"
                                        :class="a.estado === 'justificado'
                                            ? 'bg-blue-500 text-white shadow-sm'
                                            : 'text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-white'"
                                    >
                                        Justificado
                                    </button>
                                </div>
                            </td>

                            <!-- Observación -->
                            <td class="px-6 py-4">
                                <input
                                    v-model="a.observacion"
                                    type="text"
                                    placeholder="Motivo de tardanza, justificación o notas..."
                                    class="w-full px-3 py-1.5 rounded-lg border border-gray-200 dark:border-neutral-850 bg-gray-50/50 dark:bg-neutral-950/70 text-xs font-semibold focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 transition"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Botones de Guardado al Final -->
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 dark:border-neutral-800 pt-4">
                <Link
                    :href="route('supervisores.asistencia.index')"
                    class="px-4 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-neutral-800 dark:text-neutral-400 transition"
                >
                    Cancelar
                </Link>
                <button
                    type="button"
                    @click="submitAttendance"
                    :disabled="form.processing"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/20 transition disabled:opacity-50"
                >
                    Guardar Lista de Asistencia
                </button>
            </div>
        </div>
    </Alo45Layout>
</template>
