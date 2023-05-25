<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\CretorController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\DetailPlaylistController;

use App\Http\Controllers\MusicController;



// GUEST ROUTE
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);



/*
admin routes  
routes untuk admin, dimana terdapat middleware admin dan juga prefix awalan url "admin"
*/

Route::middleware(['admin.api'])->prefix('admin')->group(function () {

    // DASHBOARD -> OKE
    Route::get('dashboard', [AdminController::class, 'dashboard']);

    // User Management -> OKE
    Route::post('register', [AdminController::class, 'register']);
    Route::get('register', [AdminController::class, 'show_register']);
    Route::get('register/{id}', [AdminController::class, 'show_register_by_id']);
    Route::put('register/{id}', [AdminController::class, 'update_register']);
    Route::delete('register/{id}', [AdminController::class, 'delete_register']);
    Route::post('register/reset/{id}', [AdminController::class, 'reset_password']);

    Route::get('creator', [AdminController::class, 'request_creator']);
    Route::post('creator/{id}', [AdminController::class, 'approve_creator']);


    // Music Management
    Route::post('song/add', [AdminController::class, 'add_song']);
    Route::get('songs', [AdminController::class, 'songs_index']);
    Route::get('song/{id}', [AdminController::class, 'songs_index_id']);
    Route::put('song/edit/{id}', [AdminController::class, 'edit_song']);
    Route::delete('song/delete/{id}', [AdminController::class, 'delete_song']);

    // ALBUM -> OKE
    Route::post('album/add', [AdminController::class, 'add_album']);
    Route::get('albums', [AdminController::class, 'albums_index']);
    Route::get('albums/{id}', [AdminController::class, 'albums_index_id']);
    Route::put('albums/edit/{id}', [AdminController::class, 'edit_album']);
    Route::delete('albums/delete/{id}', [AdminController::class, 'delete_album']);
});




/*
user routes  
routes untuk user, dimana terdapat middleware admin dan juga prefix awalan url "user"
*/
Route::middleware(['user.api'])->prefix('user')->group(function () {


    //Music Management
    Route::get('song/{id}', [UserController::class, 'songs_index_id']);
    Route::get('lastplay', [UserController::class, 'last_play']);
    Route::get('trending', [UserController::class, 'trending']);
    Route::get('mood', [UserController::class, 'mood']);

    Route::post('playlists', [UserController::class, 'create_playlist']);
    Route::get('playlists', [UserController::class, 'show_all_playlist']);
    Route::get('playlist/{id}', [UserController::class, 'show_playlist']);
    Route::put('playlist/{id}', [UserController::class, 'edit_playlist']);
    Route::delete('playlist/{id}', [UserController::class, 'delete_playlist']);

    Route::post('playlist/add/song', [UserController::class, 'add_to_playlist']);
    Route::delete('playlist/remove/song/{playlist_id}/{song_id}', [UserController::class, 'remove_from_playlist']);

    // update password
    Route::put('update/{id}', [UserController::class, 'update_password']);

    Route::get('playlists/detail-playlists', [DetailPlaylistController::class, 'index']);
    Route::get('playlists/detail-playlists/{id}', [DetailPlaylistController::class, 'show']);

    //User Management
    Route::get('profile', [UserController::class, 'show_register_by_id']);
    Route::put('profile/update', [UserController::class, 'update_register']);
    Route::post('profile/creator', [UserController::class, 'request_creator']);


    //Album Management
    Route::get('albums', [UserController::class, 'albums_index']);
    Route::get('album/{id}', [UserController::class, 'albums_index_id']);

    //Search
    Route::get('search', [UserController::class, 'search']);
});





Route::middleware(['creator.api'])->prefix('creator')->group(function () {

    Route::post('playlists', [CreatorController::class, 'create_playlist']);
    Route::get('playlists', [CreatorController::class, 'show_all_playlist']);
    Route::get('playlist/{id}', [CreatorController::class, 'show_playlist']);
    Route::put('playlist/{id}', [CreatorController::class, 'edit_playlist']);
    Route::delete('playlist/{id}', [CreatorController::class, 'delete_playlist']);

    // update password
    Route::put('update/{id}', [CreatorController::class, 'update_password']);

    Route::get('playlists/detail-playlists', [DetailPlaylistController::class, 'index']);
    Route::get('playlists/detail-playlists/{id}', [DetailPlaylistController::class, 'show']);

    //User Management
    Route::get('profile', [CreatorController::class, 'show_register_by_id']);
    Route::put('profile/update', [CreatorController::class, 'update_register']);
    Route::post('profile/creator', [CreatorController::class, 'request_creator']);


    //Music Management
    Route::get('song/{id}', [CreatorController::class, 'songs_index_id']);
    Route::get('lastplay', [CreatorController::class, 'last_play']);
    Route::get('trending', [CreatorController::class, 'trending']);
    Route::get('mood', [CreatorController::class, 'mood']);

    //Album Management
    Route::get('albums', [CreatorController::class, 'albums_index']);
    Route::get('albums/{id}', [CreatorController::class, 'albums_index_id']);

    //Search
    Route::get('search', [CreatorController::class, 'search']);
});
