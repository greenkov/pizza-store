<script setup>
import Card from '@/pages/pizzas/components/Card.vue';

defineProps({
    item: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Card>
        <div class="flex h-full flex-col gap-3">
            <img
                :src="item.image_url ?? '/pizza_placeholder.png'"
                :alt="item.name"
                loading="lazy"
                class="aspect-video w-full rounded-md object-cover"
            />

            <h3 class="font-semibold">{{ item.name }}</h3>

            <ul
                class="flex-1 list-inside list-disc space-y-1 text-sm text-gray-600
  dark:text-gray-400"
            >
                <li v-for="topping in item.toppings" :key="topping.code">
                    {{ topping.name }}
                </li>
            </ul>

            <code
                class="rounded bg-gray-100 px-2 py-1 font-mono text-xs break-all
  text-gray-700 dark:bg-gray-800 dark:text-gray-300"
            >
                {{ item.topping_codes.join(',') }}
            </code>
        </div>
    </Card>
</template>

<style scoped></style>
