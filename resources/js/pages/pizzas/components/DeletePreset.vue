<script setup>
import { Form } from '@inertiajs/vue3';
import PizzaPresetController from '@/actions/App/Http/Controllers/PizzaPresetController';
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

defineProps({
    preset: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="destructive" size="sm">Delete</Button>
        </DialogTrigger>

        <DialogContent>
            <Form
                v-bind="PizzaPresetController.destroy.form(preset.id)"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>Delete "{{ preset.name }}"?</DialogTitle>
                    <DialogDescription>
                        This preset will be removed from the menu. Pizzas
                        already built from it are kept.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" type="button">
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        Delete preset
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
