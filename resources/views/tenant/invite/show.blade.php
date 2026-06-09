<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Tenant portal invite') }}</h2>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('You have been invited to join the tenant portal as :name.', ['name' => $tenant->full_name]) }}
        </p>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Register or sign in with :email to link your account.', ['email' => $tenant->email]) }}
        </p>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            {{ __('Create account') }}
        </a>
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            {{ __('Sign in') }}
        </a>
    </div>
</x-guest-layout>
