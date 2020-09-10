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

class TelegramController extends Controller
{
    //
    protected $telegram;
    protected $chat_id;
    protected $username;
    protected $text;
    protected $nama;
    protected $first_name;
    protected $keyboard;
    protected $keyboard_default;
    protected $keyboard_cari;

    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        $this->keyboard_default = [
            'inline_keyboard' => [
                [
                    ['text' => 'Konsultasi Statistik', 'callback_data' => 'konsultasi']
                ],
                [
                    ['text' => 'Pencarian Data','callback_data' => 'menucari']
                ],
                [
                    ['text' => 'Profil Saya','callback_data' => 'myprofil']
                ],
                [
                    ['text'=> 'Tentang Bot', 'callback_data'=> 'tentangbot']
                ]
            ]
        ];
        $this->keyboard_cari = [
            'inline_keyboard' => [
                [
                    ['text' => 'Publikasi', 'callback_data' => 'caripublikasi']
                ],
                [
                    ['text' => 'Subjek Statistik','callback_data' => 'caristatistik']
                ],
                [
                    ['text' => 'Berita Resmi Statistik','callback_data' => 'caribrs']
                ],
                [
                    ['text'=> 'Lainnya', 'callback_data'=> 'carilainnnya']
                ],
                [
                    ['text'=> 'Menu Awal', 'callback_data'=>'menuawal']
                ]
            ]
        ];
        $this->keyboard_cari_kembali = [
            'inline_keyboard' => [
                [
                    ['text'=> 'Kembali ke Menu Pencarian', 'callback_data'=>'menucari']
                ]
            ]
        ];
        $this->keyboard = json_encode($this->keyboard_default);
    }
 
    public function getMe()
    {
        $response = $this->telegram->getMe();
        return $response;
    }

    public function setWebHook()
    {
        $url = env('TELEGRAM_WEBHOOK_URL') . '/' . env('TELEGRAM_HASH_URL') . '/webhook';
        $response = $this->telegram->setWebhook(['url' => $url]);
 
        return $response == true ? redirect()->back() : dd($response);
    }
    public function WebHook(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate();
        if ($update->isType('callback_query')) 
        {

            /*
            menu ini kalo tombol inline keyboard ditekan
            */
            $this->text = $update->callbackQuery->data;
            $this->chat_id = $update->callbackQuery->from->id;
            $this->nama = $update->callbackQuery->from->first_name;
            if (array_key_exists("username",$update->callbackQuery->from))
            {
                $this->username = $update->callbackQuery->from->username;
            }
            else 
            {
                $this->username= $update->callbackQuery->from->first_name;
            }
           
            switch ($this->text) {
                case 'menucari':
                    $this->MenuCari();
                    break;
                case 'konsultasi':
                    $this->MenuKonsultasi();
                    break;
                case 'tentangbot':
                    $this->TentangBot();
                    break;
                case 'caripublikasi':
                    $this->CariPublikasi();
                    break;
                case 'caristatistik':
                    $this->CariStatistik();
                    break;
                case 'carilainnnya':
                    $this->CariLainnya();
                    break;
                case 'caribrs':
                    $this->CariBrs();
                    break;
                case 'myprofil':
                    $this->MyProfil();
                    break;
                default:
                $this->showMenu();
                    break;
            }
            
        } 
        else 
        {
            /*
            Pertama kali pengunjung menghubungi bot klik /start
            */
            $this->chat_id = $request['message']['chat']['id'];
            $this->first_name = $request['message']['from']['first_name'];
            $this->text = $request['message']['text'];
            if (array_key_exists("username",$request['message']['from']))
            {
                $this->username = $request['message']['from']['username'];
            }
            else 
            {
                $this->username= $this->first_name;
            }

            switch ($this->text) {
                case '/start':
                    $this->AwalStart();
                    break;
                default:
                    $this->CheckInputan();
                    break;
            }
        }

    }
    public function AwalStart()
    {
        
        $count = DataPengunjung::where('username','=',$this->username)->count();
        if ($count > 0) 
        {
            //datanya sudah ada langsung suguhkan menu
            $data = DataPengunjung::where('username','=',$this->username)->first();
            if ($data->nama == NULL)
            {
                $this->InputNama();
            }
            elseif ($data->email == NULL)
            {
                $this->InputEmail();
            }
            elseif ($data->nohp == NULL)
            {
                $this->InputHP();
            }
            else
            {
                $message = 'Anda terdaftar sebagai : <b>'.$data->nama.'</b>' .chr(10);
                $message .= 'Email : <b>'.$data->email.'</b>' .chr(10);
                $message .= 'No HP : <b>'.$data->nohp.'</b>' .chr(10);
                $this->KirimPesan($message, true);
                $this->showMenu();
            }
            
        }
        else
        {
            $message = 'Selamat datang di <b>TeleDATA</b>' .chr(10);
            $message .= '<b>BPS Provinsi Nusa Tenggara Barat</b>' .chr(10) .chr(10);
            $this->nama = $this->username;
            $data = new DataPengunjung();
            $data->username = $this->username;
            $data->chatid = $this->chat_id;
            $data->save();

            $this->KirimPesan($message,true);
            $this->InputNama();
        }
        
    }
    public function showMenu($info = false)
    { 
        $message = 'Selamat datang di <b>TeleDATA</b>' .chr(10);
        $message .= '<b>BPS Provinsi Nusa Tenggara Barat</b>' .chr(10) .chr(10);
        $message .= 'Silakan pilih layanan kami : ' .chr(10) .chr(10);
        $this->KirimPesan($message,true,true);
    }
    //nama lengkap, email, nomor hp
    public function InputEmail()
    {
        $message = "Silakan Masukkan Email anda : ";
        $this->KirimPesan($message);
    }
    public function InputHP()
    {
        $message = "Silakan Masukkan Nomor HP anda : ";
        $this->KirimPesan($message);
    }
    public function InputNama()
    {
        $message = "Silakan Masukkan Nama Lengkap";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
 
        $this->KirimPesan($message);
    }
    public function MyProfil()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
        $count = DataPengunjung::where('username','=',$this->username)->count();
        if ($count > 0) 
        {
            //datanya sudah ada langsung suguhkan menu
            $data = DataPengunjung::where('username','=',$this->username)->first();
            if ($data->nama == NULL)
            {
                $this->InputNama();
            }
            elseif ($data->email == NULL)
            {
                $this->InputEmail();
            }
            elseif ($data->nohp == NULL)
            {
                $this->InputHP();
            }
            else
            {
                $message = 'Anda terdaftar sebagai : <b>'.$data->nama.'</b>' .chr(10);
                $message .= 'Email : <b>'.$data->email.'</b>' .chr(10);
                $message .= 'No HP : <b>'.$data->nohp.'</b>' .chr(10);
                $this->KirimPesan($message, true);
                $this->showMenu();
            }
        }
        else 
        {
            $this->AwalStart();
        }
    }
    public function MenuCari()
    {
        $message = '';
        $message = 'Silakan pilih menu <b>Pencarian Data</b> dibawah ini ' .chr(10);
        $this->keyboard = json_encode($this->keyboard_cari);
        $this->KirimPesan($message,true,true);
    }
    public function CariPublikasi()
    {
        $message = "Masukkan Kata Kunci untuk Pencarian Publikasi : ";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }

    public function CariBrs()
    {
        $message = "Masukkan Kata Kunci untuk Pencarian Berita Resmi Statistik : ";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }

    public function MenuKonsultasi()
    {
        $message = '<b>Layanan Konsultasi Online</b>' .chr(10) .chr(10);
        $message .= '<b>Tidak ada Operator Online</b>' .chr(10) .chr(10);
        $message .= 'Hari Layanan :  Senin - Jumat' .chr(10);
        $message .= 'Jam Layanan : 08.00 - 15.00 WITA' .chr(10);
        $message .= 'Pesan anda akan terbaca saat operator Online' .chr(10) .chr(10);
        $message .= 'Masukkan pertanyaan untuk operator : ' .chr(10);

 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
        //$this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true);
        
    }
    public function CariStatistik()
    {
        $message = "Masukkan Kata Kunci untuk Pencarian Statistik : ";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }

    public function CariLainnya()
    {
        $message = "Masukkan Kata Kunci untuk Pencarian Lainnya : ";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }
    public function cariData()
    {
        $message = "Masukkan Kata Kunci";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
 
        $this->KirimPesan($message);
    }
    public function TentangBot()
    {
        $message ='';
        $message = '<b>TENTANG BOT TeleDATA</b>' .chr(10) .chr(10);
        $message .= 'Bot TeleData ini merupakan invoasi dari BPS Provinsi NTB.' .chr(10);
        $message .= 'memudahkan pengguna data melakukan pencarian data melalui Telegram.' .chr(10);
        $this->KirimPesan($message,true);
        $this->showMenu();
    }
    public function CheckInputan()
    {
            $tg = LogPengunjung::where('username', $this->username)->latest("updated_at")->first();
            if ($tg->command == 'InputNama') {
                $message ='';
                $message .='Nama <b>'.$this->text.'</b> berhasil disimpan' . chr(10);
                $message .='<i>Silakan masukkan email anda</i> :' . chr(10);
                $data = DataPengunjung::where('username', $this->username)->first();
                $data->nama = $this->text;
                $data->update();

                $tg->command = 'InputEmail';
                $tg->update();
 
                $this->KirimPesan($message,true);
            }
            elseif ($tg->command == 'InputEmail')
            {
                $message ='';
                $message .='Email <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                $message .='<i>Silakan Masukkan nomor HP anda</i> :' . chr(10);
                $data = DataPengunjung::where('username', $this->username)->first();
                $data->email = $this->text;
                $data->update();

                $tg->command = 'InputHP';
                $tg->update();
 
                $this->KirimPesan($message,true);
            }
            elseif ($tg->command == 'InputHP')
            {
                $message ='';
                $message .='Nomor HP <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                $data = DataPengunjung::where('username', $this->username)->first();
                $data->nohp = $this->text;
                $data->update();

                $tg->command = 'showMenu';
                $tg->update();
 
                $this->KirimPesan($message,true);
                $this->showMenu();
            }
            elseif ($tg->command == 'CariPublikasi')
            {
                
                $h = new WebApiBps();
                $keyword = rawurlencode($this->text);
                $response = $h->caripublikasi($keyword,1);
                if ($response["data-availability"]=="available")
                {
                    if ($response['data'][0]['pages'] > 1) 
                    {
                        //ada lebih 1 pages
                        $message ='';
                        $message ='Hasil Pencarian Publikasi : ' . chr(10) .chr(10);
                        for ($i = 1; $i <= 2; $i++)
                        {
                            $respon = $h->caripublikasi($keyword,$i);
                            foreach ($respon['data'][1] as $item)
                            {
                                $message .= 'Judul Publikasi : <b>'.$item["title"].'</b>' .chr(10);
                                $message .= 'Rilis : <b>'.\Carbon\Carbon::parse($item["rl_date"])->format('d M Y').'</b> | <a href="'.$item["pdf"].'">Download PDF</a> ('.$item["size"].')' .chr(10) .chr(10);
                            }
                           
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    else
                    {
                        $message ='Hasil Pencarian Publikasi : ' . chr(10) .chr(10);
                    
                        foreach ($response['data'][1] as $item)
                        {
                            
                            $message .= 'Judul Publikasi : <b>'.$item["title"].'</b>' .chr(10);
                            $message .= 'Rilis : <b>'.\Carbon\Carbon::parse($item["rl_date"])->format('d M Y').'</b> | <a href="'.$item["pdf"].'">Download PDF</a> ('.$item["size"].')' .chr(10) .chr(10);
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    
                }
                else 
                {
                    $message ='Publikasi yang anda cari tidak tersedia' .chr(10);
                    $message .= 'Ulangi pencarian publikasi' .chr(10);
                    $this->keyboard = json_encode($this->keyboard_cari_kembali);
                    $this->KirimPesan($message,true,true);
                }
                
                $tg->command = 'showMenu';
                $tg->update();
                
            }
            elseif ($tg->command == 'CariStatistik')
            {
                $h = new WebApiBps();
                $keyword = rawurlencode($this->text);
                $response = $h->caristatistik($keyword,1);
                
                if ($response["data-availability"]=="available")
                {
                    if ($response['data'][0]['pages'] > 1) 
                    {
                        //ada lebih 1 pages
                        $total_tabel = $response['data'][0]['pages'];
                        $message ='';
                        $message ='Hasil Pencarian <b>Tabel Statistik</b> : ' . chr(10) .chr(10);
                        for ($i = 1; $i <= $total_tabel; $i++)
                        {
                            $respon = $h->caristatistik($keyword,$i);
                            foreach ($respon['data'][1] as $item)
                            {
                                $message .= 'Judul Tabel : <b>'.$item["title"].'</b>' .chr(10);
                                $message .= 'Update : <b>'.\Carbon\Carbon::parse($item["updt_date"])->format('d M Y').'</b> | <a href="'.$item["excel"].'">Download Tabel</a> ('.$item["size"].')' .chr(10) .chr(10);
                            }
                           
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    else
                    {
                        $message ='Hasil Pencarian <b>Tabel Statistik</b> : ' . chr(10) .chr(10);
                    
                        foreach ($response['data'][1] as $item)
                        {
                            
                            $message .= 'Judul Tabel : <b>'.$item["title"].'</b>' .chr(10);
                            $message .= 'Update : <b>'.\Carbon\Carbon::parse($item["updt_date"])->format('d M Y').'</b> | <a href="'.$item["excel"].'">Download Tabel</a> ('.$item["size"].')' .chr(10) .chr(10);
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    
                }
                else 
                {
                    $message ='<b>Tabel Statistik</b> yang anda cari tidak tersedia' .chr(10);
                    $message .= 'Ulangi pencarian tabel statistik' .chr(10);
                    $this->keyboard = json_encode($this->keyboard_cari_kembali);
                    $this->KirimPesan($message,true,true);
                }

                $tg->command = 'showMenu';
                $tg->update();
            }
            elseif ($tg->command == 'CariBrs')
            {
                $h = new WebApiBps();
                $keyword = rawurlencode($this->text);
                $response = $h->carilain($keyword,1);
                
                if ($response["data-availability"]=="available")
                {
                    if ($response['data'][0]['pages'] > 1) 
                    {
                        //ada lebih 1 pages
                        $total_tabel = $response['data'][0]['pages'];
                        $message ='';
                        $message ='Hasil Pencarian <b>Berita Resmi Statistik</b> : ' . chr(10) .chr(10);
                        for ($i = 1; $i <= $total_tabel; $i++)
                        {
                            $respon = $h->carilain($keyword,$i);
                            foreach ($respon['data'][1] as $item)
                            {
                                $message .= 'Judul : <b>'.$item["title"].'</b>' .chr(10);
                                $message .= 'Update : <b>'.\Carbon\Carbon::parse($item["rl_date"])->format('d M Y').'</b> | <a href="'.$item["pdf"].'">Download</a> ('.$item["size"].')' .chr(10) .chr(10);
                            }
                           
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    else
                    {
                        $message ='Hasil Pencarian <b>Berita Resmi Statistik</b> : ' . chr(10) .chr(10);
                    
                        foreach ($response['data'][1] as $item)
                        {
                            
                            $message .= 'Judul : <b>'.$item["title"].'</b>' .chr(10);
                            $message .= 'Update : <b>'.\Carbon\Carbon::parse($item["rl_date"])->format('d M Y').'</b> | <a href="'.$item["pdf"].'">Download</a> ('.$item["size"].')' .chr(10) .chr(10);
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    
                }
                else 
                {
                    $message ='<b>Pencarian Berita Resmi Statistik</b> yang anda cari tidak tersedia' .chr(10);
                    $message .= 'Ulangi pencarian lainnya' .chr(10);
                    $this->keyboard = json_encode($this->keyboard_cari_kembali);
                    $this->KirimPesan($message,true,true);
                }
                $tg->command = 'showMenu';
                $tg->update();
            }
            elseif ($tg->command == 'CariLainnya')
            {
                $h = new WebApiBps();
                $keyword = rawurlencode($this->text);
                $response = $h->carilain($keyword,1);
                
                if ($response["data-availability"]=="available")
                {
                    if ($response['data'][0]['pages'] > 1) 
                    {
                        //ada lebih 1 pages
                        $total_tabel = $response['data'][0]['pages'];
                        $message ='';
                        $message ='Hasil Pencarian <b>Lainnya</b> : ' . chr(10) .chr(10);
                        for ($i = 1; $i <= $total_tabel; $i++)
                        {
                            $respon = $h->carilain($keyword,$i);
                            foreach ($respon['data'][1] as $item)
                            {
                                $message .= 'Judul : <b>'.$item["title"].'</b>' .chr(10);
                                $message .= 'Update : <b>'.\Carbon\Carbon::parse($item["rl_date"])->format('d M Y').'</b>' .chr(10) .chr(10);
                            }
                           
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    else
                    {
                        $message ='Hasil Pencarian <b>Lainnya</b> : ' . chr(10) .chr(10);
                    
                        foreach ($response['data'][1] as $item)
                        {
                            
                            $message .= 'Judul : <b>'.$item["title"].'</b>' .chr(10);
                            $message .= 'Update : <b>'.\Carbon\Carbon::parse($item["rl_date"])->format('d M Y').'</b>' .chr(10) .chr(10);
                        }
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true,true);
                    }
                    
                }
                else 
                {
                    $message ='<b>Pencarian Lainnya</b> yang anda cari tidak tersedia' .chr(10);
                    $message .= 'Ulangi pencarian lainnya' .chr(10);
                    $this->keyboard = json_encode($this->keyboard_cari_kembali);
                    $this->KirimPesan($message,true,true);
                }
                $tg->command = 'showMenu';
                $tg->update();
            }
            elseif ($tg->command == 'MenuKonsultasi')
            {
                $message ='';
                $message .='Pesan anda <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                
                $dt = new LogPesan();
                $dt->username = $this->username;
                $dt->chatid = $this->chat_id;
                $dt->isi_pesan = $this->text;
                $dt->save();

                $tg->command = 'showMenu';
                $tg->update();
 
                $this->KirimPesan($message,true);
                $this->showMenu();
            }
            else 
            {
                $message ='';
                $message .='Perintah tidak dikenali. silakan pilih menu' . chr(10) .chr(10);
                $tg->command = 'showMenu';
                $tg->update();
                $this->KirimPesan($message);
                $this->AwalStart();
            }
    }
    
    protected function KirimPesan($message, $parse_html = false, $keyboard = false)
    {
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $message,
        ];
 
        if ($parse_html) $data['parse_mode'] = 'HTML';
        if ($keyboard) $data['reply_markup'] = $this->keyboard;
 
        $this->telegram->sendMessage($data);
    }
    public function CariPub($keyword)
    {
        $h = new WebApiBps();
        $keyword = rawurlencode($keyword);
        $response = $h->caripublikasi($keyword,1);
        
        
        if ($response['data-availability']=='available')
        {
            dd($response['data'][0]);
            $hasil = array();
            foreach ($response['data'][1] as $item)
            {
                $hasil[]=array(
                    'pub_id' => $item["pub_id"],
                    'judul' => $item["title"],
                    'cover_url' => $item["cover"],
                    'pdf' => $item["pdf"]
                );
            }
            //$dd($response['data']);
        }
        else 
        {
            $hasil ='ERROR';
        }
        
        return $response;
    }
    public function cariLain($keyword)
    {
        $h = new WebApiBps();
        $keyword = rawurlencode($keyword);
        $response = $h->carilain($keyword,1);
        
        dd($response);
        if ($response['data-availability']=='available')
        {
            dd($response['data'][0]);
            $hasil = array();
            foreach ($response['data'][1] as $item)
            {
                $hasil[]=array(
                    'pub_id' => $item["pub_id"],
                    'judul' => $item["title"],
                    'cover_url' => $item["cover"],
                    'pdf' => $item["pdf"]
                );
            }
            //$dd($response['data']);
        }
        else 
        {
            $hasil ='ERROR';
        }
        
        return $response;
    }
}
