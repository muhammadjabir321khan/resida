<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLandlordSettingsRequest;
use App\Models\UserSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = [
            'default_currency' => UserSetting::getValue('default_currency', 'USD'),
            'locale' => UserSetting::getValue('locale', config('app.locale', 'en')),
            'timezone' => UserSetting::getValue('timezone', config('app.timezone', 'UTC')),
            'company_name' => UserSetting::getValue('company_name', ''),
            'date_format' => UserSetting::getValue('date_format', 'Y-m-d'),

            'stripe_publishable_key' => UserSetting::getValue('stripe_publishable_key', ''),
            'stripe_mode' => UserSetting::getValue('stripe_mode', 'test'),

            'mail_from_address' => UserSetting::getValue('mail_from_address', ''),
            'mail_from_name' => UserSetting::getValue('mail_from_name', ''),
            'smtp_host' => UserSetting::getValue('smtp_host', ''),
            'smtp_port' => UserSetting::getValue('smtp_port', ''),
            'smtp_username' => UserSetting::getValue('smtp_username', ''),
            'smtp_encryption' => UserSetting::getValue('smtp_encryption', ''),

            'tax_id' => UserSetting::getValue('tax_id', ''),
            'business_phone' => UserSetting::getValue('business_phone', ''),
        ];

        $secretsPresent = [
            'stripe_secret_key' => UserSetting::hasNonEmptyValue('stripe_secret_key'),
            'stripe_webhook_secret' => UserSetting::hasNonEmptyValue('stripe_webhook_secret'),
            'smtp_password' => UserSetting::hasNonEmptyValue('smtp_password'),
        ];

        return view('settings.edit', compact('settings', 'secretsPresent'));
    }

    public function update(UpdateLandlordSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $plainKeys = [
            'default_currency',
            'locale',
            'timezone',
            'company_name',
            'date_format',
            'stripe_publishable_key',
            'stripe_mode',
            'mail_from_address',
            'mail_from_name',
            'smtp_host',
            'smtp_username',
            'smtp_encryption',
            'tax_id',
            'business_phone',
        ];

        foreach ($plainKeys as $key) {
            $value = $data[$key] ?? null;
            if ($key === 'smtp_encryption' && $value === '') {
                UserSetting::put('smtp_encryption', null);

                continue;
            }
            UserSetting::put($key, is_string($value) ? $value : (string) $value);
        }

        if (array_key_exists('smtp_port', $data) && $data['smtp_port'] !== null) {
            UserSetting::put('smtp_port', (string) $data['smtp_port']);
        } elseif (array_key_exists('smtp_port', $data)) {
            UserSetting::put('smtp_port', null);
        }

        foreach (['stripe_secret_key', 'stripe_webhook_secret', 'smtp_password'] as $secretKey) {
            $incoming = $data[$secretKey] ?? null;
            if (is_string($incoming) && trim($incoming) !== '') {
                UserSetting::put($secretKey, trim($incoming));
            }
        }

        return redirect()
            ->route('settings.edit')
            ->with('status', __('Settings saved.'));
    }
}
