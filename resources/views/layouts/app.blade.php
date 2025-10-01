<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login GASELAG')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navigation -->
    <x-navbar />

    <div class="container mt-5 flex-grow-1">
        @yield('content')
    </div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3 bg-primary text-white">
            © 2025 Sistema de Login - Cristopher Gutierrez
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>
