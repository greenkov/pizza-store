<script setup>
import { Head } from '@inertiajs/vue3';
import CartDialog from '@/pages/pizzas/components/CartDialog.vue';
import PizzaCard from '@/pages/pizzas/components/PizzaCard.vue';

defineProps({
    presets: {
        type: Array,
        required: false,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Pizzas List" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-end">
            <CartDialog />
        </div>

        <div
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
        >
            <PizzaCard
                v-for="preset in presets"
                :key="preset.id"
                :item="preset"
            />
        </div>
    </div>
</template>

<style scoped></style>
