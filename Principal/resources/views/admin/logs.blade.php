@extends('layouts.app')

@section('title', 'Logs de Actividad')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Logs de Actividad del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    @if($logs && isset($logs['data']))
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Usuario</th>
                                        <th>Acción</th>
                                        <th>Método</th>
                                        <th>URL</th>
                                        <th>Dirección IP</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs['data'] as $log)
                                        <tr>
                                            <td>
                                                @if($log->user_id)
                                                    <span class="badge bg-info text-white">
                                                        {{ $log->user_id }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white">
                                                        Sistema
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary text-white">
                                                    {{ $log->action }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log->method === 'POST')
                                                    <span class="badge bg-success text-white">POST</span>
                                                @elseif($log->method === 'PUT')
                                                    <span class="badge bg-warning text-dark">PUT</span>
                                                @elseif($log->method === 'DELETE')
                                                    <span class="badge bg-danger text-white">DELETE</span>
                                                @else
                                                    <span class="badge bg-secondary text-white">{{ $log->method }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $log->url }}</small>
                                            </td>
                                            <td>
                                                @if($log->ip_address)
                                                    <code class="text-muted">{{ $log->ip_address }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        @if(isset($logs['links']) && $logs['links'])
                            <div class="d-flex justify-content-center mt-4">
                                {!! $logs['links'] !!}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay registros de actividad disponibles.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
