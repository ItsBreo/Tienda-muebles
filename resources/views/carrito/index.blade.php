@extends('cabecera')

@section('contenido')
    <h2> Tu carrito </h2>

    @if (empty($carrito))
        <p>No tienes películas en el carrito.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($carrito as $id => $item)
                    <tr>
                        <form method="POST" action="{{ route('carrito.eliminar', ['id' => $id,'sesionId' => $sesionId]) }}">
                            @csrf
                            <td>{{ $item['titulo'] }}</td>
                            <td>{{ number_format($item['precio'], 2) }} €</td>
                            <td>{{ $item['cantidad'] }}</td>
                            <td>{{ number_format($item['precio'] * $item['cantidad'], 2) }} €</td>
                            <td>
                                <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                            </td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Total: {{ number_format($total, 2) }} €</h4>

        <div class="mt-3">
            <form method="POST" action="{{ route('carrito.vaciar', ['sesionId' => $sesionId]) }}">
                @csrf
                <button class="btn btn-warning" type="submit" >Vaciar carrito</button>
            </form>
            <a href="{{ route('peliculas.index', ['sesionId' => $sesionId]) }}" class="btn btn-secondary">Seguir comprando</a>
        </div>
    @endif
@endsection
