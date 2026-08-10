<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import PizzaPresetController from '@/actions/App/Http/Controllers/PizzaPresetController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps({
    toppings: {
        type: Array,
        required: false,
        default: () => [],
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
            <div class="grid max-w-md gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    class="mt-1 block w-full"
                    required
                    placeholder="Margherita"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label>Toppings</Label>

                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="topping in toppings"
                        :key="topping.code"
                        class="flex items-center gap-2"
                    >
                        <Checkbox
                            :id="`topping-${topping.code}`"
                            name="topping_codes[]"
                            :value="topping.code"
                        />
                        <Label
                            :for="`topping-${topping.code}`"
                            class="font-normal"
                        >
                            {{ topping.name }}
                            <span class="text-xs text-gray-500">
                                ({{ topping.md_cal }} kcal)
                            </span>
                        </Label>
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
