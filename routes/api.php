<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('users', [AuthController::class, 'createUser']);
Route::post('login', [AuthController::class, 'login']);


/**
 * Show all the deposited transactions.
 */
Route::get('show-all-deposited-transactions',[TransactionController::class, 'showAllDepositedTransactions'])->middleware('auth:sanctum');


/**
 * deposit a new amount
 */
Route::post('deposit', [TransactionController::class, 'deposit'])->middleware('auth:sanctum');

/**
 * show all the withdrawal transactions.
 */
Route::get('show-all-withdrawal-transactions', [TransactionController::class, 'showAllWithdrawalTransactions'])->middleware('auth:sanctum');


/**
 * withdraw a new amount
 */
Route::post('withdrawal', [TransactionController::class, 'withdrawal'])->middleware('auth:sanctum');
