<?php

namespace App\Listeners;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class AuthAuditSubscriber
{
    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            AuditLog::create([
                'user_id' => $event->user->id,
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'action' => 'login',
                'description' => "Staff user [{$event->user->name}] logged in to the panel",
                'auditable_type' => User::class,
                'auditable_id' => $event->user->id,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);
        }
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            AuditLog::create([
                'user_id' => $event->user->id,
                'user_name' => $event->user->name,
                'user_email' => $event->user->email,
                'action' => 'logout',
                'description' => "Staff user [{$event->user->name}] logged out",
                'auditable_type' => User::class,
                'auditable_id' => $event->user->id,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
