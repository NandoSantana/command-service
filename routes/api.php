<?php 

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/command/deposit', [TransactionController::class, 'deposit']);
Route::post('/command/transfer', [TransactionController::class, 'transfer']);

Route::get('get', function(){
    dd('teste');
});