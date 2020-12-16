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
/*
Route::get('/', function () {
    return view('depan');
});
*/
Route::get('/', 'DepanController@dashboard')->name('awal');

Route::post(env('TELEGRAM_HASH_URL') . '/webhook', 'TelegramController@WebHook')->name('webhook');
Route::get('caripub/{keyword}', 'TelegramController@CariPub')->name('cari.pub');
Route::get('carilain/{keyword}', 'TelegramController@cariLain')->name('cari.lain');
Route::get('caribrs/{keyword}', 'TelegramController@cariBrsSaja')->name('cari.brs');

Route::group(['middleware' => ['auth']], function () {
  Route::get('set-hook', 'TelegramController@setWebHook')->name('set.webhook');
  Route::get('get-me', 'TelegramController@getMe')->name('get.me');
  Route::get('botstatus', 'TelegramController@WebhookInfo')->name('bot.status');
  Route::get('admin/list', 'AdminController@list')->name('admin.list');
  Route::post('admin/flag', 'AdminController@FlagAdmin')->name('admin.flag');
  Route::post('admin/simpan', 'AdminController@SimpanAdmin')->name('admin.simpan');
  Route::get('admin/view/{id}', 'AdminController@cariadmin')->name('admin.cari');
  Route::post('admin/updateadmin', 'AdminController@UpdateAdmin')->name('admin.update');
  Route::post('admin/gantipassword', 'AdminController@GantiPasswordAdmin')->name('admin.gantipassword');
  Route::post('admin/hapus', 'AdminController@HapusAdmin')->name('admin.hapus');
  Route::post('admin/statusonline', 'AdminController@StatusOnline')->name('admin.statusonline');
  Route::get('pengunjung/list', 'PengunjungController@list')->name('pengunjung.list');
  Route::post('pengunjung/flag', 'PengunjungController@FlagPengunjung')->name('pengunjung.flag');
  Route::post('pengunjung/hapus', 'PengunjungController@HapusPengunjung')->name('pengunjung.hapus');
  Route::post('pengunjung/kirimpesan', 'PengunjungController@KirimPesan')->name('pengunjung.kirimpesan');
  Route::get('konsultasi/list', 'KonsultasiController@list')->name('konsultasi.list');
  Route::get('konsultasi/chat/{chatid}', 'KonsultasiController@Chat')->name('konsultasi.chat');
  Route::post('konsultasi/reply', 'KonsultasiController@ReplyChat')->name('konsultasi.reply');
  Route::get('cari/list', 'CariController@list')->name('cari.list');
  Route::get('feedback/list', 'FeedbackController@list')->name('feedback.list');
});

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout')->name('logout');
