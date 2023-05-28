<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlbumsController;
use App\Http\Controllers\PlaylistsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SongsController;





// GUEST ROUTE
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);




/*
user routes  
routes untuk user, dimana terdapat middleware admin dan juga prefix awalan url "user"
*/
Route::middleware(['user.api'])->prefix('user')->group(function () {


    Route::get('song/{id}', [SongsController::class, 'songs_index_id']);
    Route::get('lastplay', [SongsController::class, 'last_play']);
    Route::get('trending', [SongsController::class, 'trending']);
    Route::get('mood', [SongsController::class, 'mood']);


    Route::post('playlists', [PlaylistsController::class, 'create_playlist']);
    Route::get('playlists', [PlaylistsController::class, 'show_all_playlist']);
    Route::get('playlist/{id}', [PlaylistsController::class, 'show_playlist']);
    Route::put('playlist/{id}', [PlaylistsController::class, 'edit_playlist']);
    Route::delete('playlist/{id}', [PlaylistsController::class, 'delete_playlist']);
    Route::post('playlist/add/song', [PlaylistsController::class, 'add_to_playlist']);
    Route::delete('playlist/remove/song/{playlist_id}/{song_id}', [PlaylistsController::class, 'remove_from_playlist']);

    Route::get('playlists/detail-playlists', [DetailPlaylistController::class, 'index']);
    Route::get('playlists/detail-playlists/{id}', [DetailPlaylistController::class, 'show']);


    //User Management
    Route::get('profile', [UserController::class, 'show_register_by_id']);
    Route::put('profile/update', [UserController::class, 'update_register']);
    Route::post('profile/creator', [UserController::class, 'request_creator']);
    Route::put('update/{id}', [UserController::class, 'update_password']);


    //Album Management
    Route::get('albums', [AlbumsController::class, 'albums_index']);
    Route::get('album/{id}', [AlbumsController::class, 'albums_index_id']);

    //Search
    Route::get('search', [SearchController::class, 'search']);
});





Route::middleware(['creator.api'])->prefix('creator')->group(function () {

    Route::get('song/{id}', [SongsController::class, 'songs_index_id']);
    Route::get('lastplay', [SongsController::class, 'last_play']);
    Route::get('trending', [SongsController::class, 'trending']);
    Route::get('mood', [SongsController::class, 'mood']);


    Route::post('playlists', [PlaylistsController::class, 'create_playlist']);
    Route::get('playlists', [PlaylistsController::class, 'show_all_playlist']);
    Route::get('playlist/{id}', [PlaylistsController::class, 'show_playlist']);
    Route::put('playlist/{id}', [PlaylistsController::class, 'edit_playlist']);
    Route::delete('playlist/{id}', [PlaylistsController::class, 'delete_playlist']);
    Route::post('playlist/add/song', [PlaylistsController::class, 'add_to_playlist']);
    Route::delete('playlist/remove/song/{playlist_id}/{song_id}', [PlaylistsController::class, 'remove_from_playlist']);

    Route::get('playlists/detail-playlists', [DetailPlaylistController::class, 'index']);
    Route::get('playlists/detail-playlists/{id}', [DetailPlaylistController::class, 'show']);


    //User Management
    Route::get('profile', [UserController::class, 'show_register_by_id']);
    Route::put('profile/update', [UserController::class, 'update_register']);
    Route::post('profile/creator', [UserController::class, 'request_creator']);
    Route::put('update/{id}', [UserController::class, 'update_password']);


    //Album Management
    Route::get('albums', [AlbumsController::class, 'albums_index']);
    Route::get('album/{id}', [AlbumsController::class, 'albums_index_id']);

    //Search
    Route::get('search', [SearchController::class, 'search']);


    Route::post('album/add', [AlbumsController::class, 'add_album']);
    Route::get('albums', [AlbumsController::class, 'albums_index']);
    Route::get('albums/{id}', [AlbumsController::class, 'albums_index_id']);
    Route::put('albums/edit/{id}', [AlbumsController::class, 'edit_album']);
    Route::delete('albums/delete/{id}', [AlbumsController::class, 'delete_album']);

    Route::post('song/add', [SongsController::class, 'add_song']);
    Route::get('songs', [SongsController::class, 'songs_index']);
    Route::get('song/{id}', [SongsController::class, 'songs_index_id']);
    Route::put('song/edit/{id}', [SongsController::class, 'edit_song']);
    Route::delete('song/delete/{id}', [SongsController::class, 'delete_song']);
});



/*
admin routes  
routes untuk admin, dimana terdapat middleware admin dan juga prefix awalan url "admin"
*/

Route::middleware(['admin.api'])->prefix('admin')->group(function () {


    Route::get('dashboard', [AdminController::class, 'dashboard']);

    Route::post('song/add', [SongsController::class, 'add_song']);
    Route::get('songs', [SongsController::class, 'songs_index']);
    Route::get('song/{id}', [SongsController::class, 'songs_index_id']);
    Route::put('song/edit/{id}', [SongsController::class, 'edit_song']);
    Route::delete('song/delete/{id}', [SongsController::class, 'delete_song']);

    // User Management -> OKE
    Route::post('register', [AdminController::class, 'register']);
    Route::get('register', [AdminController::class, 'show_register']);
    Route::get('register/{id}', [AdminController::class, 'show_register_by_id']);
    Route::put('register/{id}', [AdminController::class, 'update_register']);
    Route::delete('register/{id}', [AdminController::class, 'delete_register']);
    Route::post('register/reset/{id}', [AdminController::class, 'reset_password']);

    Route::get('creator', [AdminController::class, 'request_creator']);
    Route::post('creator/{id}', [AdminController::class, 'approve_creator']);



    Route::post('album/add', [AlbumsController::class, 'add_album']);
    Route::get('albums', [AlbumsController::class, 'albums_index']);
    Route::get('albums/{id}', [AlbumsController::class, 'albums_index_id']);
    Route::put('albums/edit/{id}', [AlbumsController::class, 'edit_album']);
    Route::delete('albums/delete/{id}', [AlbumsController::class, 'delete_album']);

    Route::post('playlists', [PlaylistsController::class, 'create_playlist']);
    Route::get('playlists', [PlaylistsController::class, 'show_all_playlist']);
    Route::get('playlist/{id}', [PlaylistsController::class, 'show_playlist']);
    Route::put('playlist/{id}', [PlaylistsController::class, 'edit_playlist']);
    Route::delete('playlist/{id}', [PlaylistsController::class, 'delete_playlist']);
    Route::post('playlist/add/song', [PlaylistsController::class, 'add_to_playlist']);
    Route::delete('playlist/remove/song/{playlist_id}/{song_id}', [PlaylistsController::class, 'remove_from_playlist']);
});
