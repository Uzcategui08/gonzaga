<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profesor;

class NotificationController extends Controller
{
    public function markAsRead($notificationId)
    {
        $profesor = Auth::user()->profesor;
        if (!$profesor) {
            return redirect()->back()->with('error', 'No se encontró profesor asociado');
        }
        
        $notification = $profesor->notifications()->findOrFail($notificationId);
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notificación marcada como leída');
    }

    public function update()
    {
        $profesor = Auth::user()->profesor;
        $newNotifications = $profesor ? $profesor->unreadNotifications()->count() > 0 : false;
        
        return response()->json([
            'newNotifications' => $newNotifications
        ]);
    }

    public function markAllAsRead()
    {
        $profesor = Auth::user()->profesor;
        if ($profesor) {
            $profesor->unreadNotifications()->update(['read_at' => now()]);
        }
        
        return redirect()->back()->with('success', 'Todas las notificaciones han sido marcadas como leídas');
    }
}
