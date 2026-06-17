<script setup>
defineProps({
    size: { type: String, default: 'md' },
    showSubtitle: { type: Boolean, default: true },
});

const sizes = {
    sm: 'h-12 w-12',
    nav: 'h-14 w-14',
    md: 'h-20 w-20',
    lg: 'h-32 w-32',
    xl: 'h-44 w-44',
};
</script>

<template>
    <div class="flex flex-col items-center select-none">
        <!-- rounded-full + overflow-hidden recorta el PNG exacto al círculo -->
        <div :class="[sizes[size] || sizes.md, 'shrink-0 rounded-full overflow-hidden']">
            <img
                src="/images/logo_alo45.png"
                alt="Logo ALÓ 45"
                class="h-full w-full object-cover"
            />
        </div>

        <p v-if="showSubtitle && size !== 'sm'"
           class="mt-2 text-center text-xs font-black uppercase tracking-widest text-alo-green">
            Taxi ALÓ45
        </p>
    </div>
</template>
