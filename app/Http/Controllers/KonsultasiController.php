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

class KonsultasiController extends Controller
{
    //
    public function list()
    {
        /*
        select data_pengunjung.username, data_pengunjung.chatid, nama, email, nohp, flag_berita,isi_pesan, jumlah_pesan, chat.created_at, chat.updated_at from data_pengunjung left join (select *, count(*) as jumlah_pesan from log_pesan GROUP by username order by created_at DESC) as chat on chat.chatid = data_pengunjung.chatid
        */
        $data = DB::table('data_pengunjung')
            ->leftJoin(\DB::Raw("(select *, count(*) as jumlah_pesan from log_pesan GROUP by username order by created_at DESC) as chat"),'data_pengunjung.chatid','=','chat.chatid')
            ->select(\DB::Raw("data_pengunjung.username, data_pengunjung.chatid, nama, email, nohp, flag_berita,isi_pesan, jumlah_pesan, chat.created_at, chat.updated_at"))
            ->orderBy('created_at','desc')
            ->get();
        //dd($data);
        return view('konsultasi.index',['dataChat'=>$data]);
    }
}
