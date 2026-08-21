<?php

namespace App\Objects;

use Arr;

class PaymentMethodPresenter
{
    /**
     * @return array<int, array{value: string, label: string, notes: array<int, string>, fields: array<int, array<string, string>>}>
     */
    public static function present(): array
    {
        $methods = [];

        foreach (config('payment.methods', []) as $code => $method) {
            $methods[] = [
                'value' => $code,
                'label' => Arr::get($method, 'label', $code),
                'notes' => self::notesFor($method),
                'fields' => self::presentFields(Arr::get($method, 'fields', [])),
            ];
        }

        return $methods;
    }

    /**
     * @return array<int, string>
     */
    public static function availableCodes(): array
    {
        return array_keys(config('payment.methods', []));
    }

    /**
     * Extra inputs the given method requires, keyed by field name.
     *
     * Returns an empty array for a method that is not configured, so an
     * unknown code adds no rules and fails on 'payment_method' alone.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function fieldsFor(?string $code): array
    {
        if ($code === null || ! in_array($code, self::availableCodes(), true)) {
            return [];
        }

        return config("payment.methods.{$code}.fields", []);
    }

    /**
     * Field descriptions for the browser. Validation rules stay on the server.
     *
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<int, array<string, string>>
     */
    private static function presentFields(array $fields): array
    {
        $presented = [];

        foreach ($fields as $name => $field) {
            $presented[] = [
                'name' => $name,
                'label' => Arr::get($field, 'label', $name),
                'type' => Arr::get($field, 'type', 'text'),
                'placeholder' => Arr::get($field, 'placeholder', ''),
                'autocomplete' => Arr::get($field, 'autocomplete', 'off'),
                'inputmode' => Arr::get($field, 'inputmode', 'text'),
                'width' => Arr::get($field, 'width', 'half'),
            ];
        }

        return $presented;
    }

    /**
     * @param  array{label?: string, fee_percent?: int|float, limit?: int|float}  $method
     * @return array<int, string>
     */
    private static function notesFor(array $method): array
    {
        $notes = [];

        if (Arr::has($method, 'fee_percent')) {
            $notes[] = __('Fee: :percent%', ['percent' => $method['fee_percent']]);
        }

        if (Arr::has($method, 'limit')) {
            $notes[] = __('Limit: $:limit', ['limit' => $method['limit']]);
        }

        return $notes;
    }
}
