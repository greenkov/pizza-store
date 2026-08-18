<script setup>
import { Form, usePage } from '@inertiajs/vue3';
import { ShoppingCart } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import CartController from '@/actions/App/Http/Controllers/CartController.ts';

const page = usePage();

const cart = computed(() => page.props.cart ?? { items: [], total: 0 });

const sizeLabels = {
    sm: 'Small',
    md: 'Medium',
    lg: 'Large',
};
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="secondary" size="sm">
                <ShoppingCart />
                Cart
                <Badge v-if="cart.items.length" variant="default">
                    {{ cart.items.length }}
                </Badge>
            </Button>
        </DialogTrigger>

        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>Your cart</DialogTitle>
                <DialogDescription>
                    Pizzas you have added so far. Nothing is ordered until you
                    check out.
                </DialogDescription>
            </DialogHeader>

            <p
                v-if="!cart.items.length"
                class="py-6 text-center text-sm text-gray-500"
            >
                Your cart is empty.
            </p>

            <ul v-else class="max-h-80 space-y-3 overflow-y-auto">
                <li
                    v-for="item in cart.items"
                    :key="item.id"
                    class="flex items-start justify-between gap-4 border-b pb-3 last:border-b-0"
                >
                    <div class="min-w-0">
                        <p class="font-medium">
                            {{ item.name }}
                            <span class="text-sm font-normal text-gray-500">
                                ({{ sizeLabels[item.size] ?? item.size }})
                            </span>
                        </p>
                        <p
                            class="truncate text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ item.toppings.join(', ') || 'No toppings' }}
                        </p>
                    </div>

                    <span class="shrink-0 font-medium"
                        >${{ item.price.toFixed(2) }}</span
                    >
                    <Form
                        v-bind="CartController.destroy.form(item.id)"
                        :options="{ preserveScroll: true }"
                        #default="{ processing }"
                    >
                        <Button
                            variant="secondary"
                            size="sm"
                            type="submit"
                            :disabled="processing"
                        >
                            {{ processing ? 'Removing…' : 'Remove' }}
                        </Button>
                    </Form>
                </li>
            </ul>

            <div
                v-if="cart.items.length"
                class="flex items-center justify-between border-t pt-3 font-semibold"
            >
                <span>Total</span>
                <span>${{ cart.total.toFixed(2) }}</span>
            </div>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary" type="button">
                        Keep shopping
                    </Button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
