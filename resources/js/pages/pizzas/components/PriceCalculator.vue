<script setup>
import { DollarSignIcon } from '@lucide/vue';
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
});

const totalPrice = computed(() => {
    const coefficient =
        props.baseSize === 'md'
            ? 1
            : (props.calcData[`${props.baseSize}_coefficient`] ?? 1);

    const toppingPrices = props.toppings.reduce(
        (sum, topping) => sum + topping.md_price,
        0,
    );

    return (
        Math.round(
            (props.calcData.md_base_price + toppingPrices) * 100 * coefficient,
        ) / 100.0
    );
});
</script>

<template>
    <Badge variant="secondary" class="px-3 py-1 text-sm">
        <DollarSignIcon class="text-orange-500" />
        {{ totalPrice }}
    </Badge>
</template>
