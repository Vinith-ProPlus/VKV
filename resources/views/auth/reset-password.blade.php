@extends('layouts.guest')

@section('content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Logo -->
        <div class="flex justify-center">
            <img src="{{ url('/assets/images/logo/logo.png') }}" alt="Logo" class="h-16 w-auto">
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                {{ __('Email') }}
            </label>
            <input id="email" class="pl-2 block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">

            @error('email')
            <span class="text-red-500 text-xs mt-2">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                {{ __('Password') }}
            </label>
            <input id="password" class="pl-2 block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   type="password" name="password" required autocomplete="new-password">

            @error('password')
            <span class="text-red-500 text-xs mt-2">{{ $message }}</span>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                {{ __('Confirm Password') }}
            </label>
            <input id="password_confirmation" class="pl-2 block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                   type="password" name="password_confirmation" required autocomplete="new-password">

            @error('password_confirmation')
            <span class="text-red-500 text-xs mt-2">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 ms-3">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
@endsection
