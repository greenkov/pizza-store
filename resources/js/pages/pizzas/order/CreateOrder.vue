<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PizzaPresetController from '@/actions/App/Http/Controllers/PizzaPresetController.ts';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import CaloriesCalculator from '@/pages/pizzas/components/CaloriesCalculator.vue';
import Card from '@/pages/pizzas/components/Card.vue';
import PaymentDialog from '@/pages/pizzas/order/PaymentDialog.vue';

defineProps({
    paymentMethods: {
        type: Array,
        required: false,
        default: () => [],
    },
});

const page = usePage();

const cart = computed(() => page.props.cart ?? { items: [], total: 0 });
const toppings = computed(() => page.props.toppings ?? []);
const calcData = computed(() => page.props.calcData ?? {});

const pizzaCount = computed(() =>
    cart.value.items.reduce((count, item) => count + item.quantity, 0),
);

const isPaymentDialogOpen = ref(false);

const toppingsForItem = (item) => {
    return item.topping_codes
        .map((code) => {
            return toppings.value.find((i) => i.code === code);
        })
        .filter((i) => !!i);
};
</script>

<template>
    <Head title="Order" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">Review your order</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Check the pizzas below, then continue to payment.
                </p>
            </div>

            <Button as-child variant="secondary" size="sm">
                <Link :href="PizzaPresetController.index()">
                    Back to menu
                </Link>
            </Button>
        </div>

        <Card v-if="!cart.items.length">
            <p class="py-6 text-center text-sm text-gray-500">
                Your cart is empty.
            </p>
        </Card>

        <div v-else class="grid items-start gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    <li
                        v-for="item in cart.items"
                        :key="item.id"
                        class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="font-medium">{{ item.name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ item.toppings.join(', ') || 'No toppings' }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <Badge variant="secondary">
                                {{ item.size.toUpperCase() }}
                            </Badge>
                            <span
                                class="text-sm text-gray-600 tabular-nums dark:text-gray-400"
                            >
                                × {{ item.quantity }}
                            </span>
                        </div>

                        <CaloriesCalculator
                            :toppings="toppingsForItem(item)"
                            :base-size="item.size"
                            :calc-data="calcData"
                            :quantity="item.quantity"
                        />

                        <span class="w-20 shrink-0 text-right font-medium">
                            ${{ item.price.toFixed(2) }}
                        </span>
                    </li>
                </ul>
            </Card>

            <Card class="lg:sticky lg:top-4">
                <div class="flex flex-col gap-4">
                    <h2 class="font-semibold">Summary</h2>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">
                            Pizzas
                        </span>
                        <span class="tabular-nums">
                            {{ pizzaCount }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between border-t pt-4 font-semibold"
                    >
                        <span>Total</span>
                        <span class="tabular-nums">
                            ${{ cart.total.toFixed(2) }}
                        </span>
                    </div>

                    <Button
                        type="button"
                        class="w-full"
                        @click="isPaymentDialogOpen = true"
                    >
                        Place order
                    </Button>
                </div>
            </Card>
        </div>
    </div>

    <PaymentDialog
        v-model:is-open="isPaymentDialogOpen"
        :payment-methods="paymentMethods"
        :total="cart.total"
    />
</template>

<style scoped></style>
