<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Iniciar Sesión') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.querySelector('.password-toggle');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const svg = passwordToggle.querySelector('svg');
            if (type === 'text') {
                svg.innerHTML = '<path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>';
            } else {
                svg.innerHTML = '<path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>';
            }
        }
    </script>
    <style>
        .login-button {
            background-color: #FFB703 !important;
            color: #333333 !important;
            transition: all 0.3s ease !important;
        }
        
        .login-button:hover {
            background-color: #FFA000 !important;
            box-shadow: 0 4px 12px rgba(255, 183, 3, 0.3) !important;
        }
        
        .login-button:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(255, 183, 3, 0.2) !important;
        }
        
        .login-input {
            border-color: #FFB703 !important;
            transition: all 0.3s ease !important;
        }
        
        .login-input:focus {
            border-color: #FFA000 !important;
            box-shadow: 0 0 0 3px rgba(255, 183, 3, 0.1) !important;
        }
        
        .password-link {
            color: #FFB703 !important;
            transition: color 0.3s ease !important;
        }
        
        .password-link:hover {
            color: #FFA000 !important;
        }
        
        .logo-container {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            margin-bottom: 1.5rem !important;
        }
        
        .error-message {
            background-color: #fee2e2 !important;
            border-color: #fecaca !important;
            color: #991b1b !important;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #FFB703;
            background: transparent;
            border: none;
            padding: 0.25rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #FFA000;
            background-color: rgba(255, 183, 3, 0.1);
            transform: translateY(-50%) scale(1.1);
        }
        
        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .input-container {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center mb-12">
            <div class="logo-container">
                <img class="h-25 w-auto" src="/storage/EXG-01.svg" alt="Laravel">
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">
                {{ __('') }}
            </h2>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('status') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-base font-semibold text-gray-900 mb-2">
                        {{ __('Usuario') }}
                    </label>
                    <div class="mt-2">
                        <div class="input-container">
                            <input id="name" name="name" type="string" autocomplete="username" required
                                class="appearance-none block w-full px-4 py-3 pl-10 border border-yellow-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm" 
                                value="{{ old('name') }}">
                        </div>
                    </div>
                    @error('name')
                        <div class="mt-2 p-3 rounded-md bg-red-100 border border-red-300">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-red-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <p class="ml-3 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            </div>
                        </div>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-base font-semibold text-gray-900 mb-2">
                        {{ __('Contraseña') }}
                    </label>
                    <div class="mt-2">
                        <div class="input-container">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                class="appearance-none block w-full px-4 py-3 pl-10 border border-yellow-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @error('password')
                        <div class="mt-2 p-3 rounded-md bg-red-100 border border-red-300">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-red-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                <p class="ml-3 text-sm text-red-700">
                                    {{ $message }}
                                </p>
                            </div>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-yellow-400 focus:ring-yellow-300 border-yellow-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                        {{ __('Recordarme') }}
                    </label>
                </div>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="password-link flex items-center text-sm font-medium transition-all duration-200">
                        <svg class="h-5 w-5 text-yellow-400 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>

            <div>
                <button type="submit" class="login-button group relative w-full flex justify-center py-3 px-6 border border-transparent text-base font-medium rounded-md text-gray-900 shadow-lg hover:shadow-xl transition-all duration-300">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-yellow-500 group-hover:text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    {{ __('Iniciar Sesión') }}
                </button>
            </div>
        </form>
    </div>
</body>
</html>