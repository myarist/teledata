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

class PengunjungController extends Controller
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
        $dataPengunjung = DataPengunjung::get();
        return view('pengunjung.index',['dataPengunjung'=>$dataPengunjung]);
    }
    public function FlagPengunjung(Request $request)
    {
        $count = DataPengunjung::where('id','=',$request->id)->count();
        $arr = array(
            'status'=>false,
            'hasil'=>'Data pengunjung tidak tersedia'
        );
        if ($count>0)
        {
            $data=DataPengunjung::where('id','=',$request->id)->first();
            if ($request->flag==1)
            {
                $flag_berita = 0;
            }
            else 
            {
                $flag_berita = 1;
            }
            $data->flag_berita = $flag_berita;
            $data->update();
            $arr = array(
                'status'=>true,
                'hasil'=>'Flag berita sudah diubah'
            );
        }
        return Response()->json($arr);
    }
    public function HapusPengunjung(Request $request)
    {
        $count = DataPengunjung::where('id','=',$request->id)->count();
        $arr = array(
            'status'=>false,
            'hasil'=>'Data pengunjung tidak tersedia'
        );
        if ($count>0)
        {
            $data = DataPengunjung::where('id','=',$request->id)->first();
            $nama = $data->username;
            $data->delete();
            $arr = array(
                'status'=>true,
                'hasil'=>'Data '.$nama.' berhasil dihapus'
            );
        }
        return Response()->json($arr);
    }
    public function KirimPesan(Request $request)
    {
        //dd($request->all());
        //save di log_pesan
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
            'username' => $request->nama,
            'chatid' => $request->chatid,
            'command' => 'MenuKonsultasi'
        ]);
        if ($result)
        {   

            $pesan_error = 'Pesan sudah dikirim ke '. $request->nama ;
            $pesan_warna = 'success';
        }
        else 
        {
            $pesan_error = '(ERROR) Pesan tidak dikirim';
            $pesan_warna = 'danger';
        }
        
        Session::flash('message', $pesan_error);
        Session::flash('message_type', $pesan_warna);
        return redirect()->route('pengunjung.list');
    }

}
