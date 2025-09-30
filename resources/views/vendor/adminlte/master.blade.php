<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
                <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

                @if(config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
                <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
                <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
                <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        @endswitch
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <style>

.main-sidebar .nav-sidebar .nav-link,
.main-sidebar .nav-sidebar .nav-link.active {
    color: #ffffff !important;
    margin-bottom: 0.7rem !important; /* Espacio entre botones */
    border-radius: 0.375rem;
}
.brand-link .brand-text, .brand-link {
    color: #ffffff !important;
}
.main-sidebar  {
    background-color: #123366 !important;
    color: #fff !important;
}
        .main-header {
    background-color: #d9bc2b !important;
    color: #ffffff !important;
}
.main-header .navbar-nav .nav-link,
.main-header .navbar-nav .nav-link.active {
    color: #ffffff !important;
}
        .select2-container--bootstrap4 .select2-selection--single {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            padding: 0.75rem 1rem !important;
            height: 46px !important;
            background-color: #fff !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            padding-left: 0 !important;
            margin: 0 !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 1rem !important;
            top: 0 !important;
            width: 30px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            background: transparent !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
            border-color: #495057 transparent transparent transparent !important;
            border-width: 8px 6px 0 6px !important;
            margin: 0 !important;
        }
        .select2-container--bootstrap4 .select2-selection--single:focus .select2-selection__arrow b {
            border-color: #007bff transparent transparent transparent !important;
        }
        .select2-container--bootstrap4 .select2-selection--single.is-invalid .select2-selection__arrow b {
            border-color: #dc3545 transparent transparent transparent !important;
        }
        .select2-container--bootstrap4 .select2-selection--single:focus {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
            outline: 0 !important;
        }
        .select2-container--bootstrap4 .select2-selection--single.is-invalid {
            border-color: #dc3545 !important;
        }
        .select2-container--bootstrap4 .select2-dropdown {
            border-radius: 0.375rem !important;
            border: 1px solid #ced4da !important;
        }
        .select2-container--bootstrap4 .select2-dropdown .select2-results__options {
            max-height: 200px !important;
            overflow-y: auto !important;
        }
        .select2-container--bootstrap4 .select2-results__option {
            padding: 0.75rem 1rem !important;
            cursor: pointer !important;
        }
        .select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
            background-color: #f8f9fa !important;
        }
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff !important;
            color: white !important;
        }
        .select2-container--bootstrap4 .select2-search__field {
            padding: 0.75rem 1rem !important;
            border-radius: 0.375rem !important;
            border: 1px solid #ced4da !important;
        }
        .select2-container--bootstrap4 .select2-dropdown--below {
            border-top: 0 !important;
        }
        .select2-container--bootstrap4 .select2-dropdown--above {
            border-bottom: 0 !important;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/datatables-custom.css') }}">

    <!-- DataTables JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/datatables-custom.css') }}">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1
    <script src="{{ asset('js/datatables-spanish.js') }}"></script>

    {{-- Custom Scripts --}}
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('select.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownAutoWidth: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            $('select.select2').each(function() {
                if ($(this).hasClass('is-invalid')) {
                    $(this).next('.select2-container').find('.select2-selection').addClass('is-invalid');
                }

                $(this).on('select2:open', function() {
                    if ($(this).hasClass('is-invalid')) {
                        $(this).next('.select2-container').find('.select2-selection').addClass('is-invalid');
                    }
                });
            });
        });

        function eliminarRegistro(button) {
            const form = $(button).closest('form');
            const nombre = $(button).data('nombre') || 'el registro';
            const tipo = $(button).data('tipo') || 'registro';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¡No podrás revertir esto! ¿Estás seguro de eliminar ${nombre}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: `Sí, eliminar ${tipo}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function showToast(type, title, text = '') {
            Swal.fire({
                position: 'top-end',
                icon: type,
                title: title,
                text: text,
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                customClass: {
                    popup: 'colored-toast',
                    title: 'font-weight-bold',
                    icon: 'mr-2'
                },
                timerProgressBar: true,
                showCloseButton: true
            });
        }

        @if (session('success'))
            showToast('success', '{{ session('success') }}');
        @endif

        @if (session('error'))
            showToast('error', '{{ session('error') }}');
        @endif

        @if (session('warning'))
            showToast('warning', '{{ session('warning') }}');
        @endif

        @if (session('info'))
            showToast('info', '{{ session('info') }}');
        @endif

        @if (session('status'))
            showToast('success', '{{ session('status') }}');
        @endif
    </script>
    @yield('adminlte_js')

</body>

</html>
