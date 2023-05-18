<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\DetailPlaylistController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// guest routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


/*
admin routes  
routes untuk admin, dimana terdapat middleware admin dan juga prefix awalan url "admin"
*/

Route::middleware(['admin.api'])->prefix('admin')->group(function () {

    // controller dan function belum di buat semua ya
    // kasih commet kalau sudah
    Route::post('register', [AdminController::class, 'register']); // sudah oke
    Route::get('register', [AdminController::class, 'show_register']); // sudah oke
    Route::post('register/{id}', [AdminController::class, 'show_register_by_id']);  // sudah oke
    Route::put('register/{id}', [AdminController::class, 'update_register']); // Sudah Oke
    Route::delete('register/{id}', [AdminController::class, 'delete_register']);

    // add song
    // Route::post('/songs', [SongController::class, 'store']);
    Route::post('songs', [SongController::class, 'store']);
    // view song
    Route::get('songs/show-all', [SongController::class, 'index']);
    Route::get('songs/{id}', [SongController::class, 'show']);

    // label done
    Route::post('/labels', [LabelController::class, 'store'])->name('labels.store');
    Route::get('/labels/show-all/', [LabelController::class, 'index']);
    Route::get('/labels/show/{id}', [LabelController::class, 'show']);

    //add  playlist

    Route::post('playlists', [PlaylistController::class, 'store']);
});




/*
user routes  
routes untuk user, dimana terdapat middleware admin dan juga prefix awalan url "user"
*/
Route::middleware(['user.api'])->prefix('user')->group(function () {
    //
    Route::get('/songs/show-all', [SongController::class, 'index']);
    Route::get('/songs/{id}', [SongController::class, 'show']);

    Route::get('/labels/show-all/', [LabelController::class, 'index']);
    Route::get('/labels/show/{id}', [LabelController::class, 'show']);

    Route::post('/playlists', [PlaylistController::class, 'create_playlist']);
    Route::get('/playlists', [PlaylistController::class, 'show_all_playlist']);
    Route::delete('playlists/{id}', [PlaylistController::class, 'delete_playlist']);
    Route::post('playlists/{id}', [PlaylistController::class, 'update_playlist']);

    // update password
    Route::put('update/{id}', [UserController::class, 'update_password']);

    Route::get('playlists/detail-playlists', [DetailPlaylistController::class, 'index']);
    Route::get('playlists/detail-playlists/{id}', [DetailPlaylistController::class, 'show']);
});
