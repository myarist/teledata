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
    protected $telegram;
    protected $chat_id;
    protected $username;
    protected $text;
    protected $nama;
    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }
    public function list()
    {
        /*
        select data_pengunjung.username, data_pengunjung.chatid, nama, email, nohp, flag_berita,isi_pesan, jumlah_pesan, chat.created_at, chat.updated_at from data_pengunjung left join (select *, count(*) as jumlah_pesan from log_pesan GROUP by username order by created_at DESC) as chat on chat.chatid = data_pengunjung.chatid
        */
        $data = DB::table('data_pengunjung')
            ->Join(\DB::Raw("(select *, count(*) as jumlah_pesan from log_pesan GROUP by username order by created_at DESC) as chat"),'data_pengunjung.chatid','=','chat.chatid')
            ->select(\DB::Raw("data_pengunjung.username, data_pengunjung.chatid, nama, email, nohp, flag_berita,isi_pesan, jumlah_pesan, chat.created_at, chat.updated_at"))
            ->orderBy('created_at','desc')
            ->get();
        //dd($data);
        return view('konsultasi.index',['dataChat'=>$data]);
    }
    public function Chat($chatid)
    {
        $data_chat = DB::table('log_pesan')
                ->Join('data_pengunjung','log_pesan.chatid','=','data_pengunjung.chatid')
                ->select('data_pengunjung.username','data_pengunjung.chatid','nama')
                ->groupBy('log_pesan.chatid')->get();
        //dd($data_chat);
        $data = DB::table('log_pesan')
        ->leftJoin(\DB::Raw("(select username as dp_username, chatid as dp_chatid, nama as dp_nama, email as dp_email, nohp as dp_nohp from data_pengunjung) as dp"),'log_pesan.chatid','=','dp.dp_chatid')
        ->where('log_pesan.chatid','=',$chatid)->orWhere('log_pesan.chatid_penerima','=',$chatid)
        ->orderBy('log_pesan.created_at','asc')
        ->get();
        //dd($data);
        return view('konsultasi.chat',['dataChat'=>$data,'dataUser'=>$data_chat,'chatid'=>$chatid]);
    }
    public function ReplyChat(Request $request)
    {
        //dd($request->all());
        //get dulu nama yg mau dikirimakn
        $data_reply = DataPengunjung::where('chatid',$request->chatid)->first();

        $data_log = new LogPesan();
        $data_log -> username = 'admin';
        $data_log -> chatid = '1';
        $data_log -> isi_pesan = $request->pesan;
        $data_log -> chatid_penerima = $request->chatid;
        $data_log -> chat_admin = 1;
        $data_log -> save();
        $data = [
            'chat_id' => $request->chatid,
            'text' => $request->pesan,
            'parse_mode' => 'HTML',
        ];
        $result = $this->telegram->sendMessage($data);
        LogPengunjung::create([
            'username' => $data_reply->username,
            'chatid' => $request->chatid,
            'command' => 'MenuKonsultasi'
        ]);
        if ($result)
        {   

            $pesan_error = 'Pesan sudah dikirim ke '. $data_reply->nama ;
            $pesan_warna = 'success';
        }
        else 
        {
            $pesan_error = '(ERROR) Pesan tidak dikirim';
            $pesan_warna = 'danger';
        }
        
        Session::flash('message', $pesan_error);
        Session::flash('message_type', $pesan_warna);
        return redirect()->route('konsultasi.chat',$request->chatid);
    }
}
