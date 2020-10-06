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
        dd($request->all());
    }

}
