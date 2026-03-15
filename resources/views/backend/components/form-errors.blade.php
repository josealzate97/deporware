@if($errors->any())
    <div class="alert alert-danger mb-4">
        <div class="fw-bold mb-1">Se encontraron errores en el formulario:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
