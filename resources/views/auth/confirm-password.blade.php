@extends('layouts.guest')

@section('content')
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Logo -->
        <div class="flex justify-center">
            <img src="{{ url('/assets/images/logo/logo.png') }}" alt="Logo" class="h-16 w-auto">
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                {{ __('Password') }}
            </label>
            <input id="password" class="pl-2 block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   type="password" name="password" required autocomplete="current-password">

            @error('password')
            <span class="text-red-500 text-xs mt-2">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-3">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
@endsection
