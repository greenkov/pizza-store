<script setup>
import { Form, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import CartController from '@/actions/App/Http/Controllers/CartController.ts';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input/index.ts';
import { Label } from '@/components/ui/label/index.ts';
import CaloriesCalculator from '@/pages/pizzas/components/CaloriesCalculator.vue';
import PriceCalculator from '@/pages/pizzas/components/PriceCalculator.vue';
import SelectedToppings from '@/pages/pizzas/components/SelectedToppings.vue';
import Topping from '@/pages/pizzas/components/Topping.vue';

const props = defineProps({
    name: {
        type: String,
        required: false,
        default: '',
    },
    initialToppingCodes: {
        type: Array,
        required: false,
        default: () => [],
    },
});

const isOpen = defineModel('open', { type: Boolean, default: false });

const page = usePage();
const toppings = computed(() => page.props.toppings ?? []);
const calcData = computed(() => page.props.calcData ?? {});

const selectedToppingsList = ref([]);

watch(isOpen, (open) => {
    if (!open) {
        return;
    }

    selectedToppingsList.value = props.initialToppingCodes
        .map((code) => toppings.value.find((topping) => topping.code === code))
        .filter(Boolean);
    selectedSize.value = 'md';
});

const isNameSpecified = computed(() => !!props.name);

const selectedSize = ref('md');

const sizes = [
    { value: 'sm', label: 'Small' },
    { value: 'md', label: 'Medium' },
    { value: 'lg', label: 'Large' },
];
const addToppingHandler = (topping) => {
    selectedToppingsList.value.push(topping);
};

const removeToppingHandler = (toppingCode) => {
    const index = selectedToppingsList.value.findIndex(
        (item) => item.code === toppingCode,
    );

    if (index > -1) {
        selectedToppingsList.value.splice(index, 1);
    }
};

const isToppingAvailable = (toppingCode) => {
    return toppingCodes.value.filter((item) => item === toppingCode).length < 2;
};

const toppingCodes = computed(() => {
    return selectedToppingsList.value.map((item) => item.code);
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-5xl">
            <DialogHeader class="space-y-3">
                <DialogTitle>{{ name || 'Custom Pizza' }}</DialogTitle>
                <DialogDescription> </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="CartController.store.form()"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <input
                    v-for="(code, index) in toppingCodes"
                    :key="index"
                    type="hidden"
                    name="topping_codes[]"
                    :value="code"
                />

                <div v-if="!isNameSpecified" class="grid max-w-md gap-2">
                    <Label for="name">Name*</Label>
                    <Input
                        id="name"
                        name="name"
                        class="mt-1 block w-full"
                        required
                        placeholder="Margherita"
                        :defaultValue="name"
                        :disabled="!!name"
                    />
                    <InputError class="mt-2" :message="errors.name" />
                </div>
                <input
                    v-else
                    type="hidden"
                    name="name"
                    :value="name"
                />

                <Label>Size</Label>
                <Label
                    v-for="option in sizes"
                    :key="option.value"
                    class="my-2 flex items-center space-x-3 font-normal"
                >
                    <input
                        v-model="selectedSize"
                        type="radio"
                        name="size"
                        :value="option.value"
                        class="size-4"
                    />
                    <span>{{ option.label }}</span>
                </Label>
                <div class="mt-6 grid grid-cols-2">
                    <div>
                        <PriceCalculator
                            class="mr-2"
                            :toppings="selectedToppingsList"
                            :base-size="selectedSize"
                            :calc-data="calcData"
                        />
                        <CaloriesCalculator
                            :toppings="selectedToppingsList"
                            :base-size="selectedSize"
                            :calc-data="calcData"
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Toppings*</Label>

                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="md:col-span-1">
                            <SelectedToppings
                                :toppings="selectedToppingsList"
                                @remove-topping="removeToppingHandler"
                            />

                            <p
                                v-if="selectedToppingsList.length === 0"
                                class="text-sm text-gray-500"
                            >
                                Nothing added yet.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <h3 class="mb-2 text-sm font-medium">Available</h3>

                            <div class="grid gap-2 sm:grid-cols-2">
                                <div
                                    v-for="topping in toppings"
                                    :key="topping.code"
                                    class="flex items-center gap-2"
                                >
                                    <Topping
                                        :topping="topping"
                                        :is-available="
                                            isToppingAvailable(topping.code)
                                        "
                                        @add-topping="addToppingHandler"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <InputError class="mt-2" :message="errors.topping_codes" />
                </div>

                <div class="flex items-center gap-4">
                    <Button :disabled="processing">Add to Cart</Button>
                    <Button
                        variant="secondary"
                        type="button"
                        @click="isOpen = false"
                    >
                        Cancel
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
