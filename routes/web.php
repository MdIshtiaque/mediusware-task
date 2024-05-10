<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::post('users', [AuthController::class, 'createUser'])->name('create.user');
Route::post('login', [AuthController::class, 'login'])->name('login');


/**
 * Show all the deposited transactions.
 */
Route::get('show-all-deposited-transactions',[TransactionController::class, 'showAllDepositedTransactions'])->name('show.all.deposited.transactions');


/**
 * deposit a new amount
 */
Route::post('deposit', [TransactionController::class, 'deposit'])->name('deposit');

/**
 * show all the withdrawal transactions.
 */
Route::get('show-all-withdrawal-transactions', [TransactionController::class, 'showAllWithdrawalTransactions'])->name('show.all.withdrawal.transactions');


/**
 * withdraw a new amount
 */
Route::post('withdrawal', [TransactionController::class, 'withdrawal'])->name('withdrawal');
