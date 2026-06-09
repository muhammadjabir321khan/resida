<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        @if (! empty($inviteTenant))
            <div class="mb-4 rounded-md bg-indigo-50 p-4 text-sm text-indigo-900">
                {{ __('You are joining the tenant portal as :name.', ['name' => $inviteTenant->full_name]) }}
            </div>
            <input type="hidden" name="invite" value="{{ $inviteToken }}" />
        @endif

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $inviteTenant?->full_name ?? '')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $inviteTenant?->email ?? '')" required autocomplete="username" @if(! empty($inviteTenant)) readonly @endif />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        @if ($roles->isNotEmpty())
            <!-- Role -->
            <div class="mt-4">
                <x-input-label for="role" :value="__('Role')" />
                @if (! empty($inviteTenant))
                    <input type="hidden" name="role" value="tenant" />
                    <div class="block mt-1 w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">tenant</div>
                @else
                    <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="" disabled @selected(old('role') === null || old('role') === '')>{{ __('Select a role') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role') === $role->name)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                @endif
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>
        @endif

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
