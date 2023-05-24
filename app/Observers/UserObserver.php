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
            'users_id' => $user->id, // Menggunakan $user->id sebagai nilai 'users_id'
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }


    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //simpan ke dalam tabel log, ini dilakukan setelah user berhasil di sunting
        Log::create([
            'logs_module' => 'sunting',
            'logs_action' => 'sunting akun',
            'users_id' => $user->email
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
            'users_id' => $user->email
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
