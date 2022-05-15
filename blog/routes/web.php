<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\XMLController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/info', function () {
    return response()->json([
        'stuff' => phpinfo()
    ]);
});

Route::get('/image/upload', [ImageController::class, 'upload']);
Route::get('/category/fetch', [CategoryController::class, 'fetch']);
Route::get('/product/fetch', [ProductController::class, 'fetch']);
Route::get('/variant/fetch', [VariantController::class, 'fetch']);
Route::get('/stock/fetch', [StockController::class, 'fetch']);
Route::get('/xml/create', [XMLController::class, 'index']);

require __DIR__.'/auth.php';
