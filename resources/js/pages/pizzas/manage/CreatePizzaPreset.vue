<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PizzaPresetController from '@/actions/App/Http/Controllers/PizzaPresetController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import CaloriesCalculator from '@/pages/pizzas/components/CaloriesCalculator.vue';
import Card from '@/pages/pizzas/components/Card.vue';
import PriceCalculator from '@/pages/pizzas/components/PriceCalculator.vue';
import SelectedToppings from '@/pages/pizzas/components/SelectedToppings.vue';
import Topping from '@/pages/pizzas/components/Topping.vue';

const props = defineProps({
    toppings: {
        type: Array,
        required: false,
        default: () => [],
    },
    initialToppingCodes: {
        type: Array,
        required: false,
        default: () => [],
    },
    calcData: {
        type: Object,
        required: true,
    },
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pizza Presets',
                href: PizzaPresetController.adminIndex(),
            },
            {
                title: 'New Preset',
                href: PizzaPresetController.create(),
            },
        ],
    },
});

const selectedToppingsList = ref(
    props.initialToppingCodes
        .map((code) => props.toppings.find((topping) => topping.code === code))
        .filter(Boolean),
);

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
    <Head title="New Pizza Preset" />

    <div class="flex flex-col space-y-6 p-4">
        <Heading
            variant="small"
            title="New Pizza Preset"
            description="Name the pizza and pick the toppings it ships with"
        />

        <Form
            v-bind="PizzaPresetController.store.form()"
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

            <div class="grid max-w-md gap-2">
                <Label for="name">Name*</Label>
                <Input
                    id="name"
                    name="name"
                    class="mt-1 block w-full"
                    required
                    placeholder="Margherita"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <Card>
                <template #header>
                    <h3><b>Calories</b> and <b>Price</b> Approximation</h3>
                    <hr class="my-4" />
                </template>
                <div class="mb-4 grid gap-2">
                    <Label>Size</Label>
                    <Label
                        v-for="option in sizes"
                        :key="option.value"
                        class="flex items-center space-x-3 font-normal"
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
                </div>
                <div class="grid gap-2 gap-y-4">
                    <CaloriesCalculator
                        :toppings="selectedToppingsList"
                        :base-size="selectedSize"
                        :calc-data="calcData"
                    />
                    <PriceCalculator
                        :toppings="selectedToppingsList"
                        :base-size="selectedSize"
                        :calc-data="calcData"
                    />
                </div>
            </Card>

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
                <Button :disabled="processing">Save</Button>

                <Link :href="PizzaPresetController.adminIndex()">
                    <Button variant="secondary" type="button">Cancel</Button>
                </Link>
            </div>
        </Form>
    </div>
</template>
