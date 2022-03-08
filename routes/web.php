<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Models\Book;
use App\Models\Author;

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

Route::get('/api/external-books', [ApiController::class, 'externalBooks']);
Route::post('/api/v1/books', [ApiController::class, 'createBook']);
Route::get('/api/v1/books', [ApiController::class, 'readBook']);
Route::patch('/api/v1/books/{id}', [ApiController::class, 'updateBook']);
Route::delete('/api/v1/books/{id}', [ApiController::class, 'deleteBook']);
Route::post('/api/v1/books/{id}/delete', [ApiController::class, 'deleteBook']);
Route::get('/api/v1/books/{id}', [ApiController::class, 'showBook']);