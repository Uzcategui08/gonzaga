<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Recuperar Contraseña') }}</title>
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center mb-12">
            <div class="logo-container">
                <img class="h-16 w-auto" src="https://laravel.com/img/logomark.min.svg" alt="Laravel">
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">
                {{ __('Recuperar Contraseña') }}
            </h2>
            <p class="mt-4 text-gray-600">
                {{ __('Ingresa tu correo electrónico y te enviaremos un enlace para recuperar tu contraseña.') }}
            </p>
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

        <form class="mt-8 space-y-6" method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="email" class="block text-base font-semibold text-gray-900 mb-2">
                        {{ __('Correo Electrónico') }}
                    </label>
                    <div class="mt-2">
                        <div class="input-container">
                            <input id="email" name="email" type="email" autocomplete="email" required
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
            </div>

            <div>
                <button type="submit" class="login-button group relative w-full flex justify-center py-3 px-6 border border-transparent text-base font-medium rounded-md text-gray-900 shadow-lg hover:shadow-xl transition-all duration-300">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-yellow-500 group-hover:text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    {{ __('Enviar Enlace de Recuperación') }}
                </button>
            </div>
        </form>
    </div>
</body>
</html>
