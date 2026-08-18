<script setup>
import { Form, Link } from '@inertiajs/vue3';
import CartController from '@/actions/App/Http/Controllers/CartController.ts';
import PizzaPresetController from '@/actions/App/Http/Controllers/PizzaPresetController.ts';
import { Button } from '@/components/ui/button/index.ts';
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
                class="flex-1 list-inside list-disc space-y-1 text-sm text-gray-600 dark:text-gray-400"
            >
                <li v-for="topping in item.toppings" :key="topping.code">
                    {{ topping.name }}
                </li>
            </ul>

            <Link
                :href="
                    PizzaPresetController.create({
                        query: {
                            topping_codes: item.topping_codes.join(','),
                        },
                    })
                "
                target="_blank"
            >
                <Button variant="secondary" size="sm">Customize</Button>
            </Link>
            <Form
                v-bind="CartController.store.form()"
                :options="{ preserveScroll: true }"
                #default="{ processing }"
            >
                <input type="hidden" name="preset_id" :value="item.id" />
                <input type="hidden" name="size" value="md" />

                <Button
                    variant="secondary"
                    size="sm"
                    type="submit"
                    :disabled="processing"
                >
                    {{ processing ? 'Adding…' : 'Add' }}
                </Button>
            </Form>
        </div>
    </Card>
</template>

<style scoped></style>
