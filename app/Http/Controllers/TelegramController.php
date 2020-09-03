<?php

namespace App\Http\Controllers;
use App\LogPengunjung;
use App\DataPengunjung;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;
use Carbon\Carbon;

class TelegramController extends Controller
{
    //
    protected $telegram;
    protected $chat_id;
    protected $username;
    protected $text;
 
    public function __construct()
    {
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
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
    public function handleRequest(Request $request)
    {
        $this->chat_id = $request['message']['chat']['id'];
        $this->username = $request['message']['from']['username'];
        $this->text = $request['message']['text'];
 
        switch ($this->text) {
            case '/start':
                $this->MenuAwal();
            break;
            case '/menu':
                $this->showMenu();
                break;
            case '/cari':
                $this->cariData();
                break;
            case '/keluar':
                $this->Keluar();
                break;
            default:
                $this->checkDatabase();
        }

    }
    public function MenuAwal()
    {
        $message = '';
        $message .= 'Selamat datang di BOT Teledata' .chr(10);
        $message .= 'BPS Provinsi NTB' .chr(10);

        $cek = DataPengunjung::where('username', $this->username)->count();
        if ($cek > 0) 
        {
            //datanya sudah ada langsung suguhkan menu
            $data = DataPengunjung::where('username', $this->username)->first();
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
                $message .= 'Anda terdaftar sebagai : <b>'.$data->nama.'</b> ';
                $this->sendMessage($message, true);
                $this->showMenu();
            }
            
        }
        else
        {
            $data = new DataPengunjung();
            $data->username = $this->username;
            $data->save();

            $this->sendMessage($message,true);
            $this->InputNama();
        }
        
    }
    public function checkInputan()
    {
        $message = '';
        $message .= 'BOT Teledata' . chr(10);
        $message .= 'BPS Provinsi NTB' . chr(10) .chr(10);
        $message .= '/menu untuk menampilkan menu' .chr(10);
        $this->sendMessage($message);
    }
    public function showMenu($info = null)
    {
        $message = '';
        if ($info) {
            $message .= $info . chr(10);
        }
        $message .= '/menu - menampilkan menu' . chr(10);
        $message .= '/cari - mencari informasi' . chr(10);
        $message .= '/operator - chat dengan operator' . chr(10) .chr(10);

        $message .= '/keluar - untuk mengakhiri' .chr(10);
 
        $this->sendMessage($message);
    }
    //nama lengkap, email, nomor hp
    public function InputEmail()
    {
        $message = "Masukkan Email anda : ";
        $this->sendMessage($message);
    }
    public function InputHP()
    {
        $message = "Masukkan Nomor HP anda : ";
        $this->sendMessage($message);
    }
    public function InputNama()
    {
        $message = "Masukkan Nama Lengkap";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
 
        $this->sendMessage($message);
    }
    public function cariData()
    {
        $message = "Masukkan Kata Kunci";
 
        LogPengunjung::create([
            'username' => $this->username,
            'command' => __FUNCTION__
        ]);
 
        $this->sendMessage($message);
    }
    public function Keluar()
    {
        $data = LogPengunjung::where('username', $this->username)->count();
        if ($data > 0)
        {
            LogPengunjung::where('username', $this->username)->delete();
        }
        $message ='';
        $message .= "Anda sudah keluar dari <b>TeleDATA</b>\n\n";
        $message .= "Terimakasih sudah menghubungi kami";
        $this->sendMessage($message,true);
    }
    public function checkDatabase()
    {
        try {
            $telegram = LogPengunjung::where('username', $this->username)->latest()->firstOrFail();
 
            if ($telegram->command == 'InputNama') {
                $message ='';
                $message .='Nama '.$this->text.' berhasil disimpan' . chr(10);
                $message .='Masukkan email anda :' . chr(10);
                $data = DataPengunjung::where('username', $this->username)->first();
                $data->nama = $this->text;
                $data->update();

                $telegram->command = 'InputEmail';
                $telegram->update();
 
                $this->sendMessage($message);
            }
            elseif ($telegram->command == 'InputEmail')
            {
                $message ='';
                $message .='Email '.$this->text.' berhasil disimpan' . chr(10) .chr(10);
                $message .='Masukkan nomor hp anda :' . chr(10);
                $data = DataPengunjung::where('username', $this->username)->first();
                $data->email = $this->text;
                $data->update();

                $telegram->command = 'InputHP';
                $telegram->update();
 
                $this->sendMessage($message);
            }
            elseif ($telegram->command == 'InputHP')
            {
                $message ='';
                $message .='Nomor HP '.$this->text.' berhasil disimpan' . chr(10) .chr(10);
                $data = DataPengunjung::where('username', $this->username)->first();
                $data->nohp = $this->text;
                $data->update();

                $telegram->command = 'showMenu';
                $telegram->update();
 
                $this->sendMessage($message);
                $this->showMenu();
            }
            elseif ($telegram->command == 'cariData')
            {
                $message ='';
                $message .='Hasil Pencarian' . chr(10) .chr(10);

                $telegram->command = 'showMenu';
                $telegram->update();
 
                $this->sendMessage($message);
                $this->showMenu();
            }
        } catch (Exception $exception) {
            $error = "Error.\n";
            $this->showMenu($error);
        }
    }
 
    protected function formatArray($data)
    {
        $formatted_data = "";
        foreach ($data as $item => $value) {
            $item = str_replace("_", " ", $item);
            if ($item == 'last updated') {
                $value = Carbon::createFromTimestampUTC($value)->diffForHumans();
            }
            $formatted_data .= "<b>{$item}</b>\n";
            $formatted_data .= "\t{$value}\n";
        }
        return $formatted_data;
    }
 
    protected function sendMessage($message, $parse_html = false)
    {
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $message,
        ];
 
        if ($parse_html) $data['parse_mode'] = 'HTML';
 
        $this->telegram->sendMessage($data);
    }
}
