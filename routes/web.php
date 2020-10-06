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
Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
  ]);
Route::get('/', function () {
    return view('depan');
});

Route::get('set-hook', 'TelegramController@setWebHook');
Route::post(env('TELEGRAM_HASH_URL') . '/webhook', 'TelegramController@WebHook')->name('webhook');
Route::get('get-me', 'TelegramController@getMe');
Route::get('caripub/{keyword}', 'TelegramController@CariPub')->name('cari.pub');
Route::get('carilain/{keyword}', 'TelegramController@cariLain')->name('cari.lain');
Route::get('caribrs/{keyword}', 'TelegramController@cariBrsSaja')->name('cari.brs');

Route::group(['middleware' => ['auth']], function () {
  Route::get('admin/list', 'AdminController@list')->name('admin.list');
  Route::get('konsultasi/list', 'KonsultasiController@list')->name('konsultasi.list');
});

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout')->name('logout');
