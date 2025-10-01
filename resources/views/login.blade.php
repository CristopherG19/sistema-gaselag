@include('includes.header')

<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="text-center">Iniciar Sesión</h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('mensaje'))
                    <div class="alert alert-success text-center">
                        {{ session('mensaje') }}
                    </div>
                @endif
                
                <form method="POST" action="{{ url('/login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="{{ old('correo') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>¿No tienes una cuenta? <a href="{{ url('/register') }}">Regístrate aquí</a></p>
                </div>
                
                <div class="text-center mt-3">
                    <a href="{{ url('/forgot-password') }}">¿Olvidaste tu contraseña?</a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')