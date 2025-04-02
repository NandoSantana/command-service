<?php 

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/deposit', [TransactionController::class, 'deposit']);
Route::post('/transfer', [TransactionController::class, 'transfer']);