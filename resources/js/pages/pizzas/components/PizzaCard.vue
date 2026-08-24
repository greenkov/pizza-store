<script setup>
import { Form } from '@inertiajs/vue3';
import { Flame, ShoppingCart } from '@lucide/vue';
import { computed } from 'vue';
import CartController from '@/actions/App/Http/Controllers/CartController.ts';
import { Button } from '@/components/ui/button/index.ts';
import Card from '@/pages/pizzas/components/Card.vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['customize']);

const customizationData = computed(() => {
    return {
        name: props.item.name + ' (custom)',
        toppings: props.item.topping_codes,
    };
});

const isHot = computed(() => props.item.hot === 1);
</script>

<template>
    <Card>
        <div class="flex h-full flex-col gap-3">
            <img
                :src="item.image_url ?? '/pizza_placeholder.png'"
                :alt="item.name"
                loading="lazy"
                class="aspect-video w-full scale-117 rounded-md object-cover object-[center_125%]"
            />

            <h3 class="flex items-center gap-1.5 font-semibold">
                {{ item.name }}
                <Flame
                    v-if="isHot"
                    class="size-4 shrink-0 text-orange-500 dark:text-orange-400"
                    aria-hidden="true"
                />
                <span v-if="isHot" class="sr-only">Hot</span>
            </h3>

            <ul
                class="flex-1 list-inside list-disc space-y-1 text-sm text-gray-600 dark:text-gray-400"
            >
                <li v-for="topping in item.toppings" :key="topping.code">
                    {{ topping.name }}
                </li>
            </ul>
            <div class="grid gap-6 md:grid-cols-3">
                <div class="md:col-span-2">
                    <Button
                        class="w-full"
                        variant="ghost"
                        size="sm"
                        @click="emit('customize', customizationData)"
                    >
                        Customize
                    </Button>
                </div>
                <div class="md:col-span-1">
                    <Form
                        v-bind="CartController.store.form()"
                        :options="{ preserveScroll: true }"
                        #default="{ processing }"
                    >
                        <input
                            type="hidden"
                            name="preset_id"
                            :value="item.id"
                        />
                        <input type="hidden" name="size" value="md" />

                        <Button
                            variant="secondary"
                            size="sm"
                            type="submit"
                            :disabled="processing"
                        >
                            <template v-if="processing">
                                …<ShoppingCart />
                            </template>
                            <template v-else> + <ShoppingCart /> </template>
                        </Button>
                    </Form>
                </div>
                <!-- 1/3 -->
            </div>
        </div>
    </Card>
</template>

<style scoped></style>
