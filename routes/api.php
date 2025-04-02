use App\Http\Controllers\TransactionController;

Route::post('/deposit', [TransactionController::class, 'deposit']);
Route::post('/transfer', [TransactionController::class, 'transfer']);