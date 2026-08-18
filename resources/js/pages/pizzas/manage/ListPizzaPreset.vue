<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PizzaPresetController from '@/actions/App/Http/Controllers/PizzaPresetController.ts';
import { Button } from '@/components/ui/button/index.ts';
import DeletePreset from '@/pages/pizzas/components/DeletePreset.vue';
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
                title: 'Pizza Presets',
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

    <div class="flex flex-col space-y-6 p-4">
        <div class="flex justify-end">
            <Button as-child variant="default" size="sm">
                <Link :href="PizzaPresetController.create()">
                    Create New Preset
                </Link>
            </Button>
        </div>

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
                    <p
                        class="truncate text-sm text-gray-600 dark:text-gray-400"
                    >
                        {{ preset.toppings.map((t) => t.name).join(', ') }}
                    </p>
                </div>

                <Link
                    :href="
                        PizzaPresetController.create({
                            query: {
                                topping_codes: preset.topping_codes.join(','),
                            },
                        })
                    "
                    target="_blank"
                >
                    <Button variant="secondary" size="sm">Duplicate</Button>
                </Link>

                <div class="flex shrink-0 items-center gap-2">
                    <Link :href="PizzaPresetController.edit(preset.id)">
                        <Button variant="secondary" size="sm">Edit</Button>
                    </Link>

                    <DeletePreset :preset="preset" />
                </div>
            </div>
        </div>
    </div>
</template>
