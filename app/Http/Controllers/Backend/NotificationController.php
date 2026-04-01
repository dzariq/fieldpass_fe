<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Models\Admin;
use App\Models\Association;
use App\Models\Club;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\DatabaseNotification;


class NotificationController extends Controller
{
    public function index(): Renderable
    {
        $notifications = auth()->user()->notifications; // All notifications
        $unreadNotifications = auth()->user()->unreadNotifications; // Only unread

        foreach ($unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return view('backend.pages.dashboard.notification', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead($id)
    {
        // Find the notification by ID
        $notification = DatabaseNotification::find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }
}
