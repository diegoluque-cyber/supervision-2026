<script setup>
import Alo45Layout from '@/Layouts/Alo45Layout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const preview = computed(() => page.props.flash?.importPreview);
const importPath = computed(() => page.props.flash?.importPath);
const importFileName = computed(() => page.props.flash?.importFileName);

const uploadForm = useForm({ file: null });
const importForm = useForm({
    import_path: '',
    replace: false,
});

const onFileChange = (e) => {
    uploadForm.file = e.target.files[0];
};

const previewFile = () => {
    uploadForm.post(route('importar.preview'), {
        forceFormData: true,
        onSuccess: () => {
            importForm.import_path = importPath.value || '';
        },
    });
};

const runImport = () => {
    importForm.import_path = importPath.value;
    importForm.post(route('importar.store'));
};

const fieldLabels = {
    unidad:           'Unidad',
    dni:              'DNI',
    nombre:           'Nombres completos',
    apellido_paterno: 'Apellido Paterno',
    apellido_materno: 'Apellido Materno',
    telefono:         'Teléfono / Celular',
};
</script>

<template>
    <Alo45Layout>
        <Head title="Importar Excel — Taxi ALÓ45" />

        <template #header>
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 blur-2xl rounded-full"></div>
                <div class="relative">
                    <h1 class="text-2xl font-bold text-white">Importar desde Excel</h1>
                    <p class="mt-1 text-sm text-gray-300/80">Sube tu archivo de conductores sin enviarlo a nadie — queda en tu PC</p>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-8">
            <!-- Paso 1 premium -->
            <div class="relative group overflow-hidden rounded-2xl bg-white/80 backdrop-blur-xl p-8 shadow-xl transition-all duration-300 hover:shadow-2xl hover:bg-white">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500/30 to-teal-500/30 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative">
                    <h2 class="mb-1 font-bold text-alo-black dark:text-white">Paso 1 — Selecciona tu archivo</h2>
                    <p class="mb-6 text-sm text-alo-gray-dark dark:text-gray-300">
                        Formatos: <strong class="text-emerald-600">.xlsx</strong>, <strong class="text-emerald-600">.xls</strong>, <strong class="text-emerald-600">.xlsm</strong> o <strong class="text-emerald-600">.csv</strong>
                    </p>

                    <div class="group relative rounded-xl border-2 border-dashed border-emerald-400/60 bg-gradient-to-br from-emerald-50/80 to-teal-50/80 dark:from-emerald-950/40 dark:to-teal-950/40 dark:border-emerald-500/40 p-8 text-center transition-all duration-300 hover:border-emerald-500 hover:bg-gradient-to-br hover:from-emerald-50 hover:to-teal-50">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100 rounded-xl"></div>
                        <input
                            type="file"
                            accept=".xlsx,.xls,.xlsm,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            class="mx-auto block text-sm relative z-10"
                            @change="onFileChange"
                        />
                        <p v-if="uploadForm.file" class="mt-3 text-sm font-medium text-emerald-600 relative z-10">
                            {{ uploadForm.file.name }}
                        </p>
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="h-12 w-12 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <InputError class="mt-3" :message="uploadForm.errors.file" />

                    <button
                        type="button"
                        :disabled="!uploadForm.file || uploadForm.processing"
                        class="mt-6 relative overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-3 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-emerald-500/25 disabled:opacity-50"
                        @click="previewFile"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-0 transition-opacity duration-300 hover:opacity-100"></div>
                        <div class="relative flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ uploadForm.processing ? 'Leyendo...' : 'Vista previa' }}
                        </div>
                    </button>
                </div>
            </div>

            <!-- Paso 2: Preview premium -->
            <div v-if="preview" class="relative group overflow-hidden rounded-2xl bg-white/80 backdrop-blur-xl p-8 shadow-xl transition-all duration-300 hover:shadow-2xl hover:bg-white">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500/30 to-cyan-500/30 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative">
                    <h2 class="mb-1 font-bold text-alo-black dark:text-white">Paso 2 — Revisa las columnas detectadas</h2>
                    <p class="mb-6 text-sm text-alo-gray-dark dark:text-gray-300">
                        Archivo: <strong class="text-blue-600 dark:text-blue-400">{{ importFileName }}</strong> — <span class="text-emerald-600 dark:text-emerald-400">{{ preview.total_rows.toLocaleString() }}</span> filas
                    </p>

                    <!-- Columnas detectadas / no detectadas premium -->
                    <div class="mb-6 grid gap-3 sm:grid-cols-2">
                        <div
                            v-for="(column, field) in preview.mapping"
                            :key="field"
                            class="group relative flex items-center gap-3 rounded-xl bg-gradient-to-r from-green-50/80 to-emerald-50/80 dark:from-green-950/40 dark:to-emerald-950/40 px-4 py-3 text-sm transition-all duration-300 hover:scale-105 hover:shadow-md"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500/20 to-teal-500/20 transition group-hover:rotate-12">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-emerald-700 dark:text-emerald-400">✓ {{ fieldLabels[field] || field }}</span>
                            <span class="text-gray-600 dark:text-gray-300 bg-gray-100/80 dark:bg-gray-800/60 backdrop-blur-xl px-2 py-1 rounded text-xs font-mono">← {{ column }}</span>
                        </div>
                    </div>

                    <!-- Columnas del Excel que no se mapearon premium -->
                    <div v-if="preview.unmapped?.length" class="mb-6 rounded-xl border border-yellow-300/50 bg-gradient-to-r from-yellow-50/80 to-amber-50/80 px-6 py-4 text-sm shadow-sm">
                        <p class="font-semibold text-yellow-800 mb-2">⚠ Columnas no reconocidas (no se importarán):</p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="col in preview.unmapped"
                                :key="col"
                                class="rounded-lg bg-yellow-200/80 px-3 py-1 text-yellow-900 font-mono text-xs font-medium"
                            >{{ col }}</span>
                        </div>
                        <p class="mt-3 text-xs text-yellow-700">
                            Si ves aquí "unidad", "celular" u otro campo importante, dile al soporte el nombre exacto de esa columna.
                        </p>
                    </div>

                    <div v-if="preview.sample?.length" class="mb-6 overflow-hidden rounded-xl border border-gray-200/50 bg-white/50 backdrop-blur-xl">
                        <div class="bg-gradient-to-r from-gray-900/50 to-gray-800/50 px-4 py-3">
                            <h3 class="text-sm font-semibold text-white">Vista previa de datos</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white">
                                        <th v-for="(val, key) in preview.sample[0]" :key="key" class="px-4 py-3 text-left font-medium">{{ fieldLabels[key] || key }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200/50">
                                    <tr v-for="(row, i) in preview.sample" :key="i" class="hover:bg-gray-50/50 transition-colors">
                                        <td v-for="(val, key) in row" :key="key" class="px-4 py-3">{{ val || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <label class="mb-6 group relative flex cursor-pointer items-center gap-3 rounded-xl bg-gray-50/80 dark:bg-gray-800/40 p-4 transition-all duration-300 hover:bg-gray-100/80 dark:hover:bg-gray-800/60">
                        <div class="relative">
                            <input v-model="importForm.replace" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                        </div>
                        <div class="relative">
                            <span class="text-sm text-alo-black dark:text-white font-medium">
                                <strong class="text-emerald-600 dark:text-emerald-400">Reemplazar</strong> todos los conductores y unidades actuales (borra los datos de prueba)
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Esta acción no se puede deshacer</p>
                        </div>
                    </label>

                    <button
                        type="button"
                        :disabled="importForm.processing"
                        class="relative overflow-hidden rounded-xl bg-gradient-to-r from-red-600 to-pink-600 px-8 py-3 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl hover:shadow-red-500/25 disabled:opacity-50"
                        @click="runImport"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-pink-400 opacity-0 transition-opacity duration-300 hover:opacity-100"></div>
                        <div class="relative flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3V7H4m4 9v3m0 0l-4-4m4 4l4-4m4 4v3m0 0l-4-4m4 4l4-4"/>
                            </svg>
                            {{ importForm.processing ? 'Importando...' : 'Importar ahora' }}
                        </div>
                    </button>
                </div>
            </div>

            <!-- Ayuda columnas -->
            <div class="rounded-xl border border-gray-200 dark:border-emerald-900/30 bg-white dark:bg-gray-900/60 p-6 text-sm text-alo-gray-dark dark:text-gray-300 shadow-card">
                <h3 class="mb-2 font-bold text-alo-black dark:text-white">Tu Excel debe tener estas columnas</h3>

                <p class="mb-2 font-semibold text-alo-black dark:text-white">Opción A — Nombre en columnas separadas:</p>
                <ul class="mb-4 list-inside list-disc space-y-1">
                    <li><strong>UNIDAD</strong></li>
                    <li><strong>DNI</strong></li>
                    <li><strong>NOMBRE</strong></li>
                    <li><strong>APELLIDO PATERNO</strong></li>
                    <li><strong>APELLIDO MATERNO</strong></li>
                    <li><strong>TELEFONO</strong> o <strong>CELULAR</strong></li>
                </ul>

                <p class="mb-2 font-semibold text-alo-black dark:text-white">Opción B — Nombre completo en una sola columna:</p>
                <ul class="list-inside list-disc space-y-1">
                    <li><strong>UNIDAD</strong></li>
                    <li><strong>DNI</strong></li>
                    <li><strong>NOMBRES COMPLETOS</strong></li>
                    <li><strong>TELEFONO</strong> o <strong>CELULAR</strong></li>
                </ul>

                <p class="mt-3 text-xs dark:text-gray-400">
                    La primera fila del Excel debe ser el encabezado con los nombres de columna.
                </p>
            </div>
        </div>
    </Alo45Layout>
</template>
