<?php

namespace App\Http\Middleware;

use App\Models\UserSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ApplyLandlordSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $locale = UserSetting::getValue('locale');
            if ($locale !== null && $locale !== '') {
                app()->setLocale($locale);
            }

            View::share('landlordCurrency', UserSetting::getValue('default_currency', 'USD'));
            View::share('landlordDateFormat', UserSetting::getValue('date_format', 'Y-m-d'));
            View::share('landlordTimezone', UserSetting::getValue('timezone', config('app.timezone', 'UTC')));
        }

        return $next($request);
    }
}
