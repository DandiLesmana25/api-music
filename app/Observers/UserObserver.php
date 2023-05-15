<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Log;

class UserObserver
{

    public function creating(User $user)
    {
        $user->last_login = now();
    }
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::create([
            'module' => 'register',
            'action' => 'registrasi akun',
            'useraccess' => $user->email

        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //simpan ke dalam tabel log, ini dilakukan setelah user berhasil di sunting
        Log::create([
            'module' => 'sunting',
            'action' => 'sunting akun',
            'useraccess' => $user->email
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleting(User $user): void
    {
        Log::create([
            'module' => 'delete',
            'action' => 'delete akun',
            'useraccess' => $user->email
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
