<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpensController;
use App\Http\Controllers\NasController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PanelController;
use App\Models\Customers;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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



Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', [UserController::class, 'user']);

    Route::prefix('plans')->group(function(){
        Route::get('/', [PlanController::class, 'index']);
        Route::get('/edit/{id}', [PlanController::class, 'edit']);
        Route::post('/create', [PlanController::class, 'store']);
        Route::post('/update/{id}', [PlanController::class, 'update']);
        Route::post('/delete/{id}', [PlanController::class, 'destroy']);
    });

    Route::prefix('customers')->group(function(){
        Route::get('/', [CustomerController::class, 'index']);
        Route::get('/list', [CustomerController::class, 'list']);
        Route::get('/edit/{id}', [CustomerController::class, 'edit']);
        Route::get('/counts', [CustomerController::class, 'count']);
        Route::post('/create', [CustomerController::class, 'store']);
        Route::post('/update/{id}', [CustomerController::class, 'update']);
        Route::post('/delete/{id}', [CustomerController::class, 'destroy']);
        Route::post('/refresh/{id}', [CustomerController::class, 'refresh']);

    });

    Route::prefix('payments')->group(function(){
        Route::get('/', [PaymentController::class, 'index']);
        Route::post('/recived', [PaymentController::class, 'payment_recived']);
        Route::post('/partial', [PaymentController::class, 'payment']);
        Route::post('/total-payment', [PaymentController::class, 'totalPayment']);
        Route::post('/recovery-payment', [PaymentController::class, 'get_recovery_payment']);
        Route::get('/pending-monthly', [PaymentController::class, 'getMonthPendingIncome']);
        Route::get('/done', [PaymentController::class, 'getDonePayment']);
    });

    Route::prefix('mikrotik')->group(function(){
        Route::get('/', [NasController::class, 'get']);
        Route::get('/edit/{id}', [NasController::class, 'getById']);
        Route::post('/create', [NasController::class, 'store']);
        Route::post('/update/{id}', [NasController::class, 'update']);
        Route::post('/delete/{id}', [NasController::class, 'delete']);
    });

    Route::prefix('panels')->group(function(){
        Route::get('/', [PanelController::class, 'getall']);
        Route::get('/edit/{id}', [PanelController::class, 'edit']);
        Route::post('/create', [PanelController::class, 'create']);
        Route::post('/update/{id}', [PanelController::class, 'update']);
        Route::post('/delete/{id}', [PanelController::class, 'delete']);
    });


    Route::prefix('expense')->group(function(){
        Route::get('/', [ExpensController::class, 'expens']);
        Route::post('/create', [ExpensController::class, 'createExpens']);
        Route::get('/edit/{id}', [ExpensController::class, 'getExpens']);
        Route::post('/update/{id}', [ExpensController::class, 'updateExpens']);
        Route::post('/delete/{id}', [ExpensController::class, 'deleteExpens']);

        Route::prefix('category')->group(function(){
            Route::get('/', [ExpensController::class, 'categories']);
            Route::post('/create', [ExpensController::class, 'createCategory']);
            Route::get('/edit/{id}', [ExpensController::class, 'getCategory']);
            Route::post('/update/{id}', [ExpensController::class, 'updateCategory']);
            Route::post('/delete/{id}', [ExpensController::class, 'deleteCategory']);
        });

    });



    Route::post('/logout', [UserController::class, 'logout']);
});


Route::group([], function(){
    Route::get('/import', [CustomerController::class, 'import']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::get('/balance-update', [PaymentController::class, 'addBalance']);
});

