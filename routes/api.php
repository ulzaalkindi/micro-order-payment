<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('orders', 'OrderController@create');
Route::get('orders', 'OrderController@index');
Route::post('webhook', 'WebhookController@midtransHandler');
