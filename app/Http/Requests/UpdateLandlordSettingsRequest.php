<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLandlordSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('default_currency')) {
            $this->merge([
                'default_currency' => strtoupper((string) $this->input('default_currency')),
            ]);
        }

        if ($this->input('smtp_port') === '' || $this->input('smtp_port') === null) {
            $this->merge(['smtp_port' => null]);
        }

        if ($this->input('mail_from_address') === '') {
            $this->merge(['mail_from_address' => null]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'default_currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'locale' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z]{2}([_-][A-Za-z0-9]+)*$/'],
            'timezone' => ['required', 'string', 'timezone:all'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'date_format' => ['required', 'string', Rule::in(['Y-m-d', 'd/m/Y', 'm/d/Y', 'd.m.Y'])],

            'stripe_publishable_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:500'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:500'],
            'stripe_mode' => ['required', 'string', Rule::in(['test', 'live'])],

            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:500'],
            'smtp_encryption' => ['nullable', 'string', Rule::in(['', 'tls', 'ssl'])],

            'tax_id' => ['nullable', 'string', 'max:64'],
            'business_phone' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'default_currency' => __('default currency'),
            'mail_from_address' => __('mail from address'),
            'smtp_encryption' => __('SMTP encryption'),
        ];
    }
}
