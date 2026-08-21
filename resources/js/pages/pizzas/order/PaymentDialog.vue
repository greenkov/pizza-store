<script setup>
import { Form } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import OrderController from '@/actions/App/Http/Controllers/OrderController.ts';
import InputError from '@/components/InputError.vue';
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
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps({
    paymentMethods: {
        type: Array,
        required: false,
        default: () => [],
    },
    total: {
        type: Number,
        required: false,
        default: 0,
    },
});

const isOpen = defineModel('isOpen', { type: Boolean, default: false });

const selectedPaymentMethod = ref(null);

watch(isOpen, (opened) => {
    if (opened) {
        selectedPaymentMethod.value = null;
    }
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader class="space-y-3">
                <DialogTitle>Payment</DialogTitle>
                <DialogDescription>
                    Choose your convenient payment method.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="OrderController.store.form()"
                :options="{
                    preserveScroll: true,
                    onSuccess: () => (isOpen = false),
                }"
                #default="{ processing, errors }"
                class="flex flex-col gap-4"
            >
                <div class="flex max-h-80 flex-col gap-2 overflow-y-auto p-1">
                    <div
                        v-for="option in paymentMethods"
                        :key="option.value"
                        class="rounded-md border transition-colors has-checked:border-primary"
                    >
                        <label
                            class="flex cursor-pointer items-center gap-3 p-4 hover:bg-accent/50"
                        >
                            <input
                                v-model="selectedPaymentMethod"
                                type="radio"
                                name="payment_method"
                                :value="option.value"
                                :disabled="processing"
                                class="size-4 accent-primary"
                            />

                            <span class="font-medium">{{ option.label }}</span>

                            <span
                                v-if="option.notes.length"
                                class="ml-auto flex flex-wrap justify-end gap-1"
                            >
                                <Badge
                                    v-for="note in option.notes"
                                    :key="note"
                                    variant="secondary"
                                >
                                    {{ note }}
                                </Badge>
                            </span>
                        </label>

                        <div
                            v-if="
                                selectedPaymentMethod === option.value &&
                                option.fields.length
                            "
                            class="grid gap-3 border-t p-4 sm:grid-cols-2"
                        >
                            <div
                                v-for="field in option.fields"
                                :key="field.name"
                                class="flex flex-col gap-1.5"
                                :class="{
                                    'sm:col-span-2': field.width === 'full',
                                }"
                            >
                                <Label :for="`${option.value}-${field.name}`">
                                    {{ field.label }}
                                </Label>

                                <Input
                                    :id="`${option.value}-${field.name}`"
                                    :name="`payment_details.${field.name}`"
                                    :type="field.type"
                                    :placeholder="field.placeholder"
                                    :autocomplete="field.autocomplete"
                                    :inputmode="field.inputmode"
                                    :disabled="processing"
                                    :aria-invalid="
                                        !!errors[
                                            `payment_details.${field.name}`
                                        ]
                                    "
                                />

                                <InputError
                                    :message="
                                        errors[`payment_details.${field.name}`]
                                    "
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <p
                    v-if="errors.payment_method"
                    class="text-sm text-destructive"
                >
                    {{ errors.payment_method }}
                </p>

                <DialogFooter
                    class="items-center border-t pt-4 sm:justify-between"
                >
                    <span class="font-semibold">
                        Total
                        <span class="tabular-nums">
                            ${{ total.toFixed(2) }}
                        </span>
                    </span>

                    <div class="flex gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary" type="button">
                                Back
                            </Button>
                        </DialogClose>

                        <Button
                            type="submit"
                            :disabled="processing || !selectedPaymentMethod"
                        >
                            {{ processing ? 'Processing…' : 'Pay now' }}
                        </Button>
                    </div>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
