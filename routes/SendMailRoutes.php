<?php
use App\Http\Controllers\Api\SendMailController;
use Illuminate\Support\Facades\Route;


Route::apiResource('mail', SendMailController::class);
Route::post('/donhang/send-mail', [SendMailController::class, 'sendPaymentSuccessMail']);
