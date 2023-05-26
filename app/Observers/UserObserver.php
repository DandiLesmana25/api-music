<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Log;

class UserObserver
{

    public function creating(User $user)
    {
        $user->users_last_login = now();
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::create([
            'logs_module' => 'register',
            'logs_action' => 'register account',
            'users_id' => $user->id,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Log::create([
            'logs_module' => 'update',
            'logs_action' => 'update last login',
            'users_id' => $user->id
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleting(User $user): void
    {
        Log::create([
            'logs_module' => 'delete',
            'logs_action' => 'delete akun',
            'users_id' => $user->id
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Implementasi jika perlu
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Implementasi jika perlu
    }
}
