<x-guest-layout>
    <form method="POST" action="{{ route('dashboard') }}">
        @csrf

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="onetime_token" :value="__('認証コード')" />
            <x-text-input id="onetime_token" class="block w-full mt-1" type="number" name="onetime_token" :value="old('onetime_token')" required/>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ml-4">
                {{ __('ログイン / 新規登録') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
