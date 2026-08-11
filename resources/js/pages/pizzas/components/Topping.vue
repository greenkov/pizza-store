<script setup>
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

const props = defineProps({
    topping: {
        type: Object,
        required: true,
    },
    isAvailable: {
        type: Boolean,
        required: false,
        default: false,
    },
});

const emit = defineEmits(['addTopping']);

const addTopping = () => {
    emit('addTopping', props.topping);
};

</script>

<template>
    <Button :id="`topping-${topping.code}`" type="button" @click="addTopping" :disabled="!isAvailable">+</Button>
    <Label :for="`topping-${topping.code}`" class="font-normal">
        {{ topping.name }}
        <span class="text-xs text-gray-500"> ({{ topping.md_cal }} kcal) </span>
    </Label>
</template>

<style scoped></style>
