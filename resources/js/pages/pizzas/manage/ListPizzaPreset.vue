<script setup>
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PizzaPresetController from '@/actions/App/Http/Controllers/PizzaPresetController.ts';
import { Button } from '@/components/ui/button/index.ts';
import { edit } from '@/routes/profile/index.ts';

defineProps({
    presets: {
        type: Array,
        required: false,
        default: () => [],
    },
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Create Pizza Preset',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Pizza Presets" />

    <div class="flex flex-col gap-3">
        <div
            v-for="preset in presets"
            :key="preset.id"
            class="flex items-center gap-4 rounded-md border border-gray-200 p-3 dark:border-gray-800"
        >
            <img
                :src="preset.image_url ?? '/pizza_placeholder.png'"
                :alt="preset.name"
                class="size-16 shrink-0 rounded object-cover"
            />

            <div class="min-w-0 flex-1">
                <h3 class="font-semibold">{{ preset.name }}</h3>
                <p class="truncate text-sm text-gray-600 dark:text-gray-400">
                    {{ preset.toppings.map((t) => t.name).join(', ') }}
                </p>
            </div>

            <code class="shrink-0 font-mono text-xs text-gray-500">
                {{ preset.topping_codes.join(',') }}
            </code>

            <div class="flex shrink-0 items-center gap-2">
                <Link :href="PizzaPresetController.edit(preset.id)">
                    <Button variant="secondary" size="sm">Edit</Button>
                </Link>

                <Form v-bind="PizzaPresetController.destroy.form(preset.id)">
                    <Button type="submit" variant="destructive" size="sm"
                        >Delete</Button
                    >
                </Form>
            </div>
        </div>
    </div>
</template>
