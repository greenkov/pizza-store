<script setup>
import { Flame } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    toppings: {
        type: Array,
        required: true,
    },
    baseSize: {
        type: String,
        default: 'md',
    },
    calcData: {
        type: Object,
        required: true,
    },
    quantity: {
        type: Number,
        required: false,
        default: 1,
    },
});

const calories = computed(() => {
    const coefficient =
        props.baseSize === 'md'
            ? 1
            : (props.calcData[`${props.baseSize}_coefficient`] ?? 1);

    const toppingCalories = props.toppings.reduce(
        (sum, topping) => sum + topping.md_cal,
        0,
    );

    return Math.round(
        (props.calcData.md_base_cal + toppingCalories) * coefficient * props.quantity,
    );
});
</script>

<template>
    <Badge variant="secondary" class="px-3 py-1 text-sm">
        <Flame class="text-orange-500" />
        {{ calories }} kcal
    </Badge>
</template>
