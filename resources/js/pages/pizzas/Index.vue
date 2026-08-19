<script setup>
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import CartDialog from '@/pages/pizzas/components/CartDialog.vue';
import CustomPizzaCard from '@/pages/pizzas/components/CustomPizzaCard.vue';
import CustomPizzaDialog from '@/pages/pizzas/components/CustomPizzaDialog.vue';
import PizzaCard from '@/pages/pizzas/components/PizzaCard.vue';

const isBuilderOpen = ref(false);

defineProps({
    presets: {
        type: Array,
        required: false,
        default: () => [],
    },
});

const startCustomization = (data) => {
    console.log(data);
    customizationData.name = data.name;
    customizationData.toppings = data.toppings;
    console.log(customizationData);
    isBuilderOpen.value = true;
};

const customizationData = reactive({
    name: 'Custom Pizza Blend',
    toppings: [],
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
            <CustomPizzaCard @customize="startCustomization" />

            <PizzaCard
                v-for="preset in presets"
                :key="preset.id"
                :item="preset"
                @customize="startCustomization"
            />
        </div>
    </div>

    <CustomPizzaDialog
        v-model:open="isBuilderOpen"
        :name="customizationData.name"
        :initial-topping-codes="customizationData.toppings"
    />
</template>

<style scoped></style>
