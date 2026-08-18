<script setup>
import { Form, router, usePage } from '@inertiajs/vue3';
import { Minus, Plus, ShoppingCart } from '@lucide/vue';
import { computed, ref } from 'vue';
import CartController from '@/actions/App/Http/Controllers/CartController.ts';
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

const page = usePage();

const cart = computed(() => page.props.cart ?? { items: [], total: 0 });

const sizeLabels = {
    sm: 'Small',
    md: 'Medium',
    lg: 'Large',
};

const MIN_QUANTITY = 1;
const MAX_QUANTITY = 99;

// Id of the item whose quantity request is still in flight, so only that row
// gets disabled rather than the whole cart.
const pendingId = ref(null);

const setQuantity = (item, quantity) => {
    if (
        !Number.isInteger(quantity) ||
        quantity < MIN_QUANTITY ||
        quantity > MAX_QUANTITY ||
        quantity === item.quantity
    ) {
        // Re-render the row so a rejected keyboard edit snaps back.
        pendingId.value = null;

        return;
    }

    pendingId.value = item.id;

    router.patch(
        CartController.update.url(item.id),
        { quantity },
        {
            preserveScroll: true,
            onFinish: () => (pendingId.value = null),
        },
    );
};

const increaseQuantity = (item) => setQuantity(item, item.quantity + 1);

const decreaseQuantity = (item) => setQuantity(item, item.quantity - 1);
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

                    <div class="flex shrink-0 items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            type="button"
                            aria-label="Decrease quantity"
                            :disabled="
                                pendingId === item.id ||
                                item.quantity <= MIN_QUANTITY
                            "
                            @click="decreaseQuantity(item)"
                        >
                            <Minus />
                        </Button>

                        <input
                            :key="item.quantity"
                            type="number"
                            class="h-9 w-14 [appearance:textfield] rounded-md border bg-transparent text-center text-sm [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                            :min="MIN_QUANTITY"
                            :max="MAX_QUANTITY"
                            :value="item.quantity"
                            :disabled="pendingId === item.id"
                            :aria-label="`Quantity for ${item.name}`"
                            @change="
                                setQuantity(item, Number($event.target.value))
                            "
                        />

                        <Button
                            variant="outline"
                            size="icon"
                            type="button"
                            aria-label="Increase quantity"
                            :disabled="
                                pendingId === item.id ||
                                item.quantity >= MAX_QUANTITY
                            "
                            @click="increaseQuantity(item)"
                        >
                            <Plus />
                        </Button>
                    </div>

                    <span class="w-16 shrink-0 text-right font-medium"
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
