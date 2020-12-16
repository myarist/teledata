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
use Illuminate\Support\Facades\Validator;
use Telegram\Bot\FileUpload\InputFile;
use App\LogFeedback;

class DepanController extends Controller
{
    //
    public function dashboard()
    {
        $data_cari = LogCari::selectRaw('command,count(*) as jumlah')->groupBy('command')->get();
        //dd($data_cari);
        $d_cari = array();
        foreach ($data_cari as $item)
        {
            if ($item->command == 'CariPublikasi')
            {
                $label = 'Publikasi';
            }
            elseif ($item->command == 'CariBrs')
            {
                $label = 'BRS';
            }
            elseif ($item->command == 'CariStatistik')
            {
                $label = 'Data Statistik';
            }
            else
            {
                $label = 'Lainnya';
            }
            $d_cari[] = array('label'=>$label,'value'=>$item->jumlah);
        }
        $hari_tgl = array();
        foreach (\Carbon\CarbonPeriod::between(\Carbon\Carbon::parse(now())->subDay(7), now()) as $item)
        {
            $jumlah_pengunjung = \DB::table('data_pengunjung')->whereDate('created_at','<=',$item->format('Y-m-d'))->count();
            $hari_tgl[]=array('tanggal'=>$item->format('d M'),'jumlah'=>$jumlah_pengunjung);
        }
        $data_bulan = array(
            1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'
        );
        $bulan = date('n');
        $tahun = date('Y');
        $data_konsul = array();
        for ($i=1,$thn=$tahun,$j=$bulan; $i <= 6; $i++)
        {
           $jumlah_konsul = LogPesan::whereMonth('created_at','<=',$j)->whereYear('created_at','=',$thn)->count();
           $data_konsul[]=array('tanggal'=>$data_bulan[$j] .' '.$thn,'jumlah'=>$jumlah_konsul);
           $j = $j-1;
           if ($j == 0)
           {
              $j = 12;
              $thn = $thn -1;
           }
        }
        $data_konsul = array_reverse($data_konsul);
        $data_konsul = collect($data_konsul)->pluck('jumlah');
        $d_konsul = LogPesan::get();
        //data_pengunjung perbulan
        $data_pengunjung = array();
        for ($i=1,$thn=$tahun,$j=$bulan; $i <= 6; $i++)
        {
           $jumlah_pengunjung = LogPengunjung::whereMonth('created_at','<=',$j)->whereYear('created_at','=',$thn)->count();
           $data_pengunjung[]=array('tanggal'=>$data_bulan[$j] .' '.$thn,'jumlah'=>$jumlah_pengunjung);
           $j = $j-1;
           if ($j == 0)
           {
              $j = 12;
              $thn = $thn -1;
           }
        }
        $data_pengunjung = array_reverse($data_pengunjung);
        //batas
        $feed = LogFeedback::orderBy('created_at','desc')->get();
        //dd($hari_tgl,json_encode($data_konsul));
        return view('depan',['dataFeedback'=>$feed,'dataChart'=>json_encode($data_pengunjung),'dataDonut'=>json_encode($d_cari),'dataKonsul'=>$d_konsul,'dataKonsulChart'=>json_encode($data_konsul)]);
    }
}
