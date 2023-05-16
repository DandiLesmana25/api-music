<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\ViewSongController;

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
    Route::put('register/{id}', [AdminController::class, 'update_register']);
    Route::delete('register/{id}', [AdminController::class, 'delete_register']);

    // add song
    Route::post('/songs', [SongController::class, 'store']);

    // add label
    Route::post('/labels', [LabelController::class, 'store'])->name('labels.store');
});




/*
user routes  
routes untuk user, dimana terdapat middleware admin dan juga prefix awalan url "user"
*/
Route::middleware(['user.api'])->prefix('user')->group(function () {
    //
    Route::prefix('viewsongs')->group(function () {
        Route::get('/', [ViewSongController::class, 'index']);

        // Route::post('/', [ViewSongController::class, 'store']);
        // Route::get('/{viewsong}', [ViewSongController::class, 'show']);
        // Route::put('/{viewsong}', [ViewSongController::class, 'update']);
        // Route::delete('/{viewsong}', [ViewSongController::class, 'destroy']);
    });

});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
