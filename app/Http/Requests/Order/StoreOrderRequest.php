<?php

namespace App\Http\Requests\Order;

use App\Objects\PaymentMethodPresenter;
use Arr;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Str;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Details are validated against the picked method only, so a Visa payload
     * never has to satisfy the PayPal rules and vice versa.
     *
     * @return array[]
     */
    public function rules(): array
    {
        $availableMethodsString = implode(',', PaymentMethodPresenter::availableCodes());

        $rules = [
            'payment_method' => ['required', 'string', "in:{$availableMethodsString}"],
            'payment_details' => ['array'],
        ];

        foreach ($this->requiredDetailFields() as $name => $field) {
            $rules["payment_details.{$name}"] = ['required', ...Arr::get($field, 'rules', [])];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->requiredDetailFields() as $name => $field) {
            $attributes["payment_details.{$name}"] = Str::lower(Arr::get($field, 'label', $name));
        }

        return $attributes;
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->rejectExpiredCard($validator);
            },
        ];
    }

    /**
     * Card numbers are typed in groups, but only the digits are validated.
     */
    protected function prepareForValidation(): void
    {
        $cardNumber = $this->input('payment_details.card_number');

        if (! is_string($cardNumber)) {
            return;
        }

        $this->merge([
            'payment_details' => array_replace($this->input('payment_details', []), [
                'card_number' => preg_replace('/\D/', '', $cardNumber),
            ]),
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function requiredDetailFields(): array
    {
        return PaymentMethodPresenter::fieldsFor($this->input('payment_method'));
    }

    /**
     * 'date_format:m/y' accepts 01/20, so the month itself is checked here.
     */
    private function rejectExpiredCard(Validator $validator): void
    {
        $expiresAt = $this->input('payment_details.expires_at');

        if (! is_string($expiresAt) || $validator->errors()->has('payment_details.expires_at')) {
            return;
        }

        $lastValidDay = CarbonImmutable::createFromFormat('!m/y', $expiresAt)->endOfMonth();

        if ($lastValidDay->isPast()) {
            $validator->errors()->add('payment_details.expires_at', __('The card has expired.'));
        }
    }
}
