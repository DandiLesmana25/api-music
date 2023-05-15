<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
    Route::post('register', [AdminController::class, 'register']);
    Route::get('register', [AdminController::class, 'show_register']);
    Route::get('register/{id}', [AdminController::class, 'show_register_by_id']);
    Route::put('register/{id}', [AdminController::class, 'update_register']);
    Route::delete('register/{id}', [AdminController::class, 'delete_register']);
});




/*
user routes  
routes untuk admin, dimana terdapat middleware admin dan juga prefix awalan url "user"
*/
Route::middleware(['user.api'])->prefix('user')->group(function () {
    //

});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
