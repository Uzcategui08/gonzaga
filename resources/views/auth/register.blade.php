<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Registro') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        .password-link {
            color: #FFB703 !important;
            transition: color 0.3s ease !important;
        }

        .password-link:hover {
            color: #FFA000 !important;
        }
    </style>
    <script>
        function togglePassword(button) {
            const input = button.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);

            const svg = button.querySelector('svg');
            if (type === 'text') {
                svg.innerHTML = '<path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>';
            } else {
                svg.innerHTML = '<path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordButtons = document.querySelectorAll('.password-toggle');
            passwordButtons.forEach(button => {
                button.addEventListener('click', function() {
                    togglePassword(this);
                });
            });
        });
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center mb-12">
            <div class="logo-container flex justify-center items-center">
                <img class="h-16 w-auto" src="https://laravel.com/img/logomark.min.svg" alt="Laravel">
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">
                {{ __('Registro') }}
            </h2>
            <p class="mt-4 text-gray-600">
                {{ __('Crea una nueva cuenta en el sistema.') }}
            </p>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-base font-semibold text-gray-900 mb-2">
                        {{ __('Nombre') }}
                    </label>
                    <div class="mt-2">
                        <div class="input-container">
                            <input id="name" name="name" type="text" required autofocus autocomplete="name"
                                class="appearance-none block w-full px-4 py-3 login-input rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm"
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
                    <label for="email" class="block text-base font-semibold text-gray-900 mb-2">
                        {{ __('Correo Electrónico') }}
                    </label>
                    <div class="mt-2">
                        <div class="input-container">
                            <input id="email" name="email" type="email" required autocomplete="username"
                                class="appearance-none block w-full px-4 py-3 login-input rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm"
                                value="{{ old('email') }}">
                        </div>
                    </div>
                    @error('email')
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
                        <div class="input-container relative">
                            <input id="password" name="password" type="password" required autocomplete="new-password"
                                class="appearance-none block w-full px-4 py-3 login-input rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm">
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

                <div>
                    <label for="password_confirmation" class="block text-base font-semibold text-gray-900 mb-2">
                        {{ __('Confirmar Contraseña') }}
                    </label>
                    <div class="mt-2">
                        <div class="input-container relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                class="appearance-none block w-full px-4 py-3 login-input rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 sm:text-sm">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @error('password_confirmation')
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

            <div>
                <button type="submit" class="login-button group relative w-full flex justify-center py-3 px-6 border border-transparent text-base font-medium rounded-md text-gray-900 shadow-lg hover:shadow-xl transition-all duration-300">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-yellow-500 group-hover:text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    {{ __('Crear Cuenta') }}
                </button>
            </div>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            {{ __('¿Ya tienes una cuenta?') }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="password-link font-medium">
                        {{ __('Iniciar Sesión') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
