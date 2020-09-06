<?php

namespace App\Http\Controllers;
use App\LogPengunjung;
use App\DataPengunjung;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;
use Carbon\Carbon;
use Telegram\Bot\Keyboard\Keyboard;

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
                    $this->Konsultasi();
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
                $message = 'Anda terdaftar sebagai : <b>'.$data->nama.'</b> ';
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
        $message = "Masukkan Email anda : ";
        $this->KirimPesan($message);
    }
    public function InputHP()
    {
        $message = "Masukkan Nomor HP anda : ";
        $this->KirimPesan($message);
    }
    public function InputNama()
    {
        $message = "Masukkan Nama Lengkap";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
 
        $this->KirimPesan($message);
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

    public function Konsultasi()
    {
        $message ='';
        $message = '<b>Layanan Konsultasi Online</b>' .chr(10) .chr(10);
        $message .= '<b>Tidak ada Operator Online</b>' .chr(10);
        $message .= 'Pesan anda akan terbaca saat operator Online' .chr(10);
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
                $message .='Masukkan email anda :' . chr(10);
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
                $message .='Masukkan nomor hp anda :' . chr(10);
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
                $message ='';
                $message .='Hasil Pencarian Publikasi : ' . chr(10) .chr(10);

                $tg->command = 'showMenu';
                $tg->update();
 
                $this->KirimPesan($message);
                $this->showMenu();
            }
            elseif ($tg->command == 'CariStatistik')
            {
                $message ='';
                $message .='Hasil Pencarian Statistik : ' . chr(10) .chr(10);

                $tg->command = 'showMenu';
                $tg->update();
 
                $this->KirimPesan($message);
                $this->showMenu();
            }
            elseif ($tg->command == 'CariLainnya')
            {
                $message ='';
                $message .='Hasil Pencarian Lainnya : ' . chr(10) .chr(10);

                $tg->command = 'showMenu';
                $tg->update();
 
                $this->KirimPesan($message);
                $this->showMenu();
            }
            elseif ($tg->command == 'Konsultasi')
            {
                $message ='';
                $message .='Pesan anda <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                
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
                $this->showMenu();
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
}
