<?php

namespace App\Http\Controllers;

use App\LogPengunjung;
use App\DataPengunjung;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;
use Carbon\Carbon;
use Telegram\Bot\Keyboard\Keyboard;
use App\LogPesan;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use App\Helpers\WebApiBps;
use App\LogCari;
use App\User;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\PesanChannel;
class PesanChannelController extends Controller
{
    //
    protected $telegram;
    protected $chat_id;
    protected $username;
    protected $text;
    protected $nama;
    protected $namachannel;
    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $this->chan_id = env('TELEGRAM_CHANNEL_ID');
    }
    public function list()
    {
        $data = PesanChannel::orderBy('created_at','desc')->get();
        return view('channel.index',[
            'dataPesan'=> $data
        ]);
    }
    public function KirimPesanChannel(Request $request)
    {
        $data_pesan = str_replace("<br>","\n",$request->pesan);
        $data_pesan = str_replace("<div>"," ",$data_pesan);
        $data_pesan = str_replace("</div>","",$data_pesan);
        //$data_pesan = urlencode($request->pesan);
        //dd($data_pesan);
        $data = [
            'chat_id' => $this->chan_id,
            'text' => $data_pesan,
            'parse_mode' => 'HTML',
        ];
        $result = $this->telegram->sendMessage($data);

        if ($result)
        {
            //simpan ke tabel pesan
            $data = new PesanChannel();
            $data->username = Auth::user()->username;
            $data->chatid = Auth::user()->chatid_tg;
            $data->isi_pesan = $request->pesan;
            $data->save();
            $pesan_error = 'Pesan sudah dikirim ke channel' ;
            $pesan_warna = 'success';
        }
        else
        {
            $pesan_error = '(ERROR) Pesan tidak dikirim';
            $pesan_warna = 'danger';
        }

        Session::flash('message', $pesan_error);
        Session::flash('message_type', $pesan_warna);
        return redirect()->route('channel.list');
    }
}
