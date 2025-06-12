@php
    $profesor = auth()->user()->profesor;
    $unreadNotifications = $profesor ? $profesor->unreadNotifications : collect();
@endphp

<li class="nav-item dropdown notifications-menu">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
        <i class="far fa-bell icon-lg"></i>
        @if($unreadNotifications->count() > 0)
            <span class="badge badge-danger badge-counter">{{ $unreadNotifications->count() }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="width: 320px;">
        <span class="dropdown-header">
            <i class="fas fa-bell mr-1"></i>Notificaciones
            @if($unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline float-right">
                    @csrf
                    <button type="submit" class="btn btn-xs btn-link text-dark p-0">
                        <small><i class="fas fa-check-double"></i> Marcar todas</small>
                    </button>
                </form>
            @endif
        </span>
        <div class="dropdown-divider"></div>
        
        @forelse($unreadNotifications as $notification)
            <div class="dropdown-item notification-item">
                <div class="media">
                    <div class="media-left">
                        <span class="notification-icon bg-warning">
                            <i class="fas fa-exclamation-circle"></i>
                        </span>
                    </div>
                    <div class="media-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="notification-title">{{ $notification->data['estudiante'] ?? 'N/A' }}</h6>
                        </div>
                        <p class="notification-text">
                            <small>
                                <strong>Materia:</strong> {{ $notification->data['materia'] ?? 'N/A' }}<br>
                                <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($notification->data['fecha'])->format('d/m/Y') }}<br>
                                <strong>Hora:</strong> {{ \Carbon\Carbon::parse($notification->data['hora_llegada'])->format('H:i') }}<br>
                                <strong>Motivo:</strong> {{ $notification->data['motivo'] }}<br>
                                <strong>Creado:</strong> {{ \Carbon\Carbon::parse($notification->created_at)->locale('es')->diffForHumans() }}<br>
                            </small>
                        </p>
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-outline-success">
                                <i class="fas fa-check mr-1"></i> Marcar como leída
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="dropdown-divider"></div>
        @empty
            <div class="dropdown-item text-center py-3">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <p class="mb-0">No hay notificaciones nuevas</p>
            </div>
        @endforelse
    </div>
</li>

<style>
    .notifications-menu .icon-lg {
        font-size: 1.25rem;
        position: relative;
    }

    .badge-counter {
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 0.6rem;
        font-weight: 400;
        padding: 3px 5px;
        min-width: 18px;
        line-height: 1;
        border: 2px solid #fff;
        border-radius: 10px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }
    
    .notification-item {
        padding: 10px;
        transition: background-color 0.3s;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
    }
    
    .notification-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: white;
        margin-right: 10px;
    }
    
    .notification-title {
        font-weight: 600;
        margin-bottom: 5px;
        color: #343a40;
    }
    
    .notification-text {
        margin-bottom: 8px;
        color: #6c757d;
        font-size: 0.85rem;
    }

    .dropdown-menu-lg {
        max-height: 400px;
        overflow-y: auto;
    }
</style>