<script setup>
import { X } from '@lucide/vue';

defineProps({
    toppings: {
        type: Array,
        required: false,
        default: () => [],
    },
});

defineEmits(['removeTopping']);
</script>

<template>
    <ul class="list-inside list-disc space-y-1 text-sm">
        <li v-for="(topping, index) in toppings" :key="index">
            <span class="inline-flex items-center gap-2 align-middle">
                {{ topping.name }}
                <span class="text-xs text-gray-500">
                    ({{ topping.md_cal }} kcal)
                </span>

                <button
                    type="button"
                    class="rounded p-0.5 text-gray-400 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-800"
                    :aria-label="`Remove ${topping.name}`"
                    @click="$emit('removeTopping', topping.code)"
                >
                    <X class="size-3.5" />
                </button>
            </span>
        </li>
    </ul>
</template>
