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

const sizes = [
    { value: 'sm', label: 'SM' },
    { value: 'md', label: 'MD' },
    { value: 'lg', label: 'LG' },
];

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

const setSize = (item, size) => {
    // console.log(item.name, size);
    // return;
    if (Object.hasOwn(sizes, size)) {
        pendingId.value = null;

        return;
    }

    pendingId.value = item.id;

    router.patch(
        CartController.update.url(item.id),
        { size },
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

        <DialogContent class="sm:max-w-3xl">
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

            <ul
                v-else
                class="grid max-h-80 grid-cols-[minmax(0,1fr)_auto_auto_auto] items-center gap-x-4 gap-y-3 overflow-y-auto"
            >
                <li
                    v-for="item in cart.items"
                    :key="item.id"
                    class="col-span-4 grid grid-cols-subgrid items-center border-b pb-3 last:border-b-0"
                >
                    <div class="min-w-0">
                        <p class="font-medium">
                            {{ item.name }}
                        </p>
                        <p
                            class="truncate text-sm text-gray-600 dark:text-gray-400"
                        >
                            {{ item.toppings.join(', ') || 'No toppings' }}
                        </p>
                    </div>

                    <div class="flex flex-col items-center gap-2">
                        <div class="flex items-center gap-1">
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
                                    setQuantity(
                                        item,
                                        Number($event.target.value),
                                    )
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

                        <div class="inline-flex gap-1 rounded-md border p-1">
                            <label
                                v-for="option in sizes"
                                :key="option.value"
                                class="cursor-pointer"
                            >
                                <input
                                    type="radio"
                                    :name="`size-${item.id}`"
                                    :value="option.value"
                                    :checked="item.size === option.value"
                                    class="peer sr-only"
                                    @change="setSize(item, option.value)"
                                />
                                <span
                                    class="block rounded px-4 py-1.5 text-sm font-medium transition-colors peer-checked:bg-primary peer-checked:text-primary-foreground peer-focus-visible:ring-2 peer-focus-visible:ring-ring hover:bg-accent"
                                >
                                    {{ option.label }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <span class="text-right font-medium"
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
