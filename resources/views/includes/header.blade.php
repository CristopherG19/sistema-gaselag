<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login GASELAG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100"> <!-- Agrega estas clases -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container d-flex justify-content-center align-items-center">
            <!-- Logo centrado -->
            <a href="{{ url('/login') }}">
                <img src="{{ asset('assets/image/logo_gaselag_white.png') }}" alt="Logo GASELAG" class="logo-navbar img-fluid" style="max-height: 115px;">
            </a>
        </div>
    </nav>

    <div class="container mt-5 flex-grow-1"><!-- Agrega flex-grow-1 -->