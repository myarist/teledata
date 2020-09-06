<?php

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
/*
Route::post('/3Nb71akPKQTM3jSK2BdLxT1VGq1FHfRquaGKJMTP/webhook', function () {
    $update = Telegram::commandsHandler(true);
    dd($update);
});
*/
//Route::post('/3Nb71akPKQTM3jSK2BdLxT1VGq1FHfRquaGKJMTP/webhook','TelegramController@handleRequest')->name('webhook');
Route::get('set-hook', 'TelegramController@setWebHook');
Route::post(env('TELEGRAM_HASH_URL') . '/webhook', 'TelegramController@WebHook')->name('webhook');
Route::get('get-me', 'TelegramController@getMe');