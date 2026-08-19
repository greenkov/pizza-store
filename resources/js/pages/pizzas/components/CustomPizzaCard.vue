<script setup>
import { Button } from '@/components/ui/button/index.ts';
import Card from '@/pages/pizzas/components/Card.vue';

defineProps({
    name: {
        type: String,
        required: false,
        default: 'Custom pizza',
    },
    toppingsList: {
        type: Array,
        required: false,
        default: () => [],
    },
});

const emit = defineEmits(['customize']);
</script>

<template>
    <Card>
        <div class="flex h-full flex-col gap-3">
            <img
                src="/pizza_placeholder.png"
                :alt="name"
                loading="lazy"
                class="aspect-video w-full rounded-md object-cover"
            />

            <h3 class="font-semibold">{{ name }}</h3>

            <ul
                v-if="toppingsList.length"
                class="flex-1 list-inside list-disc space-y-1 text-sm text-gray-600 dark:text-gray-400"
            >
                <li v-for="topping in toppingsList" :key="topping">
                    {{ topping }}
                </li>
            </ul>

            <p v-else class="flex-1 text-sm text-gray-600 dark:text-gray-400">
                Pick your own toppings and add the pizza straight to the cart.
            </p>

            <Button variant="secondary" size="sm" @click="emit('customize', {})">Build your own</Button>
        </div>
    </Card>
</template>

<style scoped></style>
