@include('includes.header')

<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="text-center">Recuperar contraseña</h3>
            </div>
            <div class="card-body">
                @if (session('mensaje'))
                    <div class="alert alert-success text-center">
                        {{ session('mensaje') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url('/forgot-password') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Enviar enlace</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="{{ url('/login') }}">Volver al login</a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.footer')