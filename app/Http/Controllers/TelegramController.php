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
                ],
                [
                    ['text'=> 'Selesai', 'callback_data'=> 'selesai']
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
        $this->keyboard_edit_profil = [
            'inline_keyboard' => [
                [
                    ['text' => 'Edit Nama', 'callback_data' => 'editnama'],
                    ['text' => 'Edit Email', 'callback_data' => 'editemail'],
                    ['text' => 'Edit No HP', 'callback_data' => 'editnohp']
                ],
                [
                    ['text'=> 'Edit Profil', 'callback_data'=>'editprofil']
                ],
                [
                    ['text'=> 'Menu Awal', 'callback_data'=>'menuawal']
                ]
            ]
        ];
        $this->keyboard_edit_profil_admin = [
            'inline_keyboard' => [
                [
                    ['text' => 'Edit Nama', 'callback_data' => 'editnama'],
                    ['text' => 'Edit Email', 'callback_data' => 'editemail'],
                    ['text' => 'Edit No HP', 'callback_data' => 'editnohp']
                ],
                [
                    ['text'=> 'Edit Profil', 'callback_data'=>'editprofil']
                ],
                [
                    ['text'=> 'List Pengunjung', 'callback_data'=>'logpengunjung'],
                    ['text'=> 'Log Pencarian', 'callback_data'=>'logpencarian'],
                    ['text'=> 'Update ID-TG', 'callback_data'=>'updateidtg']
                ],
                [
                    ['text'=> 'Menu Awal', 'callback_data'=>'menuawal']
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
           
            if (array_key_exists("username",$update['callback_query']['from']))
            {
                $this->username =  $update->callbackQuery->from->username;
            }
            else 
            {
                $this->username=  $update->callbackQuery->from->first_name;
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
                case 'editnama':
                    $this->EditNama();
                    break;
                case 'editemail':
                    $this->EditEmail();
                    break;
                case 'editnohp':
                    $this->EditNoHp();
                    break;
                case 'editprofil':
                    $this->InputNama();
                    break;
                case 'selesai':
                    $this->Selesai();
                    break;
                case 'logpengunjung':
                    $this->LogDataPengunjung();
                    break;
                case 'logpencarian':
                    $this->LogDataPencarian();
                    break;
                case 'updateidtg':
                    $this->UpdateIdTele();
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
        
        $count = DataPengunjung::where('chatid','=',$this->chat_id)->count();
        if ($count > 0) 
        {
            //datanya sudah ada langsung suguhkan menu
            $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
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
                $message = 'Anda terdaftar sebagai : ' .chr(10);
                $message .= 'Nama : <b>'.$data->nama.'</b>' .chr(10);
                $message .= 'Email : <b>'.$data->email.'</b>' .chr(10);
                $message .= 'No HP : <b>'.$data->nohp.'</b>' .chr(10);
                $this->KirimPesan($message, true);
                $this->showMenu();
            }
            
        }
        else
        {
            $message = 'Selamat datang di <b>TeleDATA (Telegram Data BPSNTB)</b>' .chr(10);
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
        $message = 'Selamat datang di <b>TeleDATA (Telegram Data BPSNTB)</b>' .chr(10);
        $message .= '<b>BPS Provinsi Nusa Tenggara Barat</b>' .chr(10) .chr(10);
        $message .= 'Silakan <b>Pilih Layanan</b> yang tersedia : ' .chr(10) .chr(10);
        $this->KirimPesan($message,true,true);
    }
    //nama lengkap, email, nomor hp
    public function InputEmail()
    {
        $message = "Silakan Masukkan Email anda : ";
        $this->KirimPesan($message);
    }
    public function EditEmail()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
        $message = "Silakan Masukkan Email anda : ";
        $this->KirimPesan($message);
    }
    public function InputHP()
    {
        $message = "<i>Silakan Masukkan Nomor HP anda</i> : ";
        $this->KirimPesan($message,true);
    }
    public function Selesai()
    {
        $count = LogPengunjung::where('chatid', $this->chat_id)->count();
        if ($count > 0)
        {
            LogPengunjung::where('chatid', $this->chat_id)->delete();
        }
        $message = "<b>Terimakasih Telah Menggunakan Layanan Kami</b>" .chr(10);
        
        $this->KirimPesan($message,true);
    }
    public function EditNoHp()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
        $message = "<i>Silakan Masukkan Nomor HP anda</i> : ";
        $this->KirimPesan($message,true);
    }
    public function InputNama()
    {
        $message = "<i>Silakan Masukkan Nama Lengkap</i> :";
 
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
 
        $this->KirimPesan($message,true);
    }
    public function EditNama()
    {
        $message = "<i>Silakan Masukkan Nama Lengkap</i> :";
 
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
 
        $this->KirimPesan($message,true);
    }
    public function MyProfil()
    {
        $count = DataPengunjung::where('chatid','=',$this->chat_id)->count();
        if ($count > 0) 
        {
            //datanya sudah ada langsung suguhkan menu
            $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
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
                $message = 'Anda terdaftar sebagai : ' .chr(10);
                $message .= 'Nama : <b>'.$data->nama.'</b>' .chr(10);
                $message .= 'Email : <b>'.$data->email.'</b>' .chr(10);
                $message .= 'No HP : <b>'.$data->nohp.'</b>' .chr(10);
                $cek_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->count();
                if ($cek_admin > 0)
                {
                    //admin dan tampilkan keyboard
                    $message .= chr(10).'Role : Admin Sistem <b>TeleData</b> ('.$this->username.')' .chr(10);
                    $this->keyboard = json_encode($this->keyboard_edit_profil_admin);
                }
                else 
                {
                    //keyboard biasa
                    $this->keyboard = json_encode($this->keyboard_edit_profil);
                }
                
                
                $this->KirimPesan($message,true,true);
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
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }

    public function CariBrs()
    {
        $message = "Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Berita Resmi Statistik</b> : ";
 
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }

    public function MenuKonsultasi()
    {
        $message = '<b>Layanan Konsultasi Online</b>' .chr(10) .chr(10);
        $message .= '<b>Tidak ada Operator Online</b>' .chr(10) .chr(10);
        $message .= 'Hari Layanan :  Senin - Jumat (Kecuali hari libur)' .chr(10);
        $message .= 'Jam Layanan : 08.00 - 15.00 WITA' .chr(10);
        $message .= 'Pesan anda akan terbaca saat operator Online' .chr(10) .chr(10);
        $message .= '<i>Masukkan pertanyaan untuk operator</i> : ' .chr(10);

 
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
        //$this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true);
        
    }
    public function CariStatistik()
    {
        $message = "Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Statistik</b> : ";
 
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }

    public function CariLainnya()
    {
        $message = "Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Lainnya</b> : ";
 
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        
    }
    
    public function TentangBot()
    {
        $message ='';
        $message = '<b>TENTANG BOT TeleDATA (Telegram Data BPSNTB)</b>' .chr(10) .chr(10);
        $message .= 'Bot TeleData ini merupakan invoasi dari BPS Provinsi Nusa Tenggara Barat.' .chr(10);
        $message .= 'memudahkan pengguna data melakukan pencarian data melalui Telegram.' .chr(10);
        $message .= 'dikembangkan oleh Bidang IPDS' .chr(10);
        $this->KirimPesan($message,true);
        $this->showMenu();
    }
    public function LogDataPencarian()
    {
        $cek = LogCari::count();
        if ($cek > 0)
        {
            $data = LogCari::take(-30)->get();
            $message = 'Data 30 Keyword Pencarian terakhir di TeleData' .chr(10);
            $i=1;
            foreach ($data as $item) {
                $message .= $i.'. Nama: <b>'.$item->username .'</b> | Keyword: ('.$item->command.') <b>'. $item->keyword .'</b> | tanggal: '. \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') .chr(10);
                $i++;
            }
        }
        else 
        {
            $message = 'Data Log Pencarian masih kosong' .chr(10);
        }
        
       $this->KirimPesan($message,true);
       $this->MyProfil();
    }
    public function LogDataPengunjung()
    {
        $data = DataPengunjung::take(-30)->get();
        $message = 'Data 30 Pengunjung TeleData terakhir' .chr(10);
        $i=1;
        foreach ($data as $item) {
            $message .= $i.'. Nama: <b>'.$item->nama .'</b> | Email: <b>'. $item->email .'</b> | No HP: <b>'.$item->nohp.'</b> | Daftar: <b>'. \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') .'</b>' .chr(10) .chr(10);
            $i++;
        }
       $this->KirimPesan($message,true);
       $this->MyProfil();

    }
    public function UpdateIdTele()
    {
        $cek = User::where('user_tg','=',$this->username)->count();
        if ($cek > 0)
        {
            //username admin ada dan update id telegram
            $data = User::where('user_tg','=',$this->username)->first();
            $data->chatid_tg = $this->chat_id;
            $data->update();

            $message ='Data ID Telegram admin <b>'.$this->username.'</b> sudah di update'.chr(10);
        }
        else 
        {
            //bukan admin
            $message ='Anda bukan admin sistem'.chr(10);
        }
        $this->KirimPesan($message,true);
        $this->MyProfil();
    }
    public function CheckInputan()
    {
            $cek = LogPengunjung::where('chatid','=',$this->chat_id)->count();
            if ($cek > 0 )
            {
                $tg = LogPengunjung::where('chatid','=',$this->chat_id)->latest("updated_at")->first();
                if ($tg->command == 'InputNama') {
                    $message ='';
                    $message .='Nama <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                    $message .='<i>Silakan masukkan email anda</i> :' . chr(10);
                    $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                    $data->nama = $this->text;
                    $data->update();

                    $tg->command = 'InputEmail';
                    $tg->update();
    
                    $this->KirimPesan($message,true);
                }
                elseif ($tg->command == 'EditNama') {
                    $message ='';
                    $message .='Nama <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                    $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                    $data->nama = $this->text;
                    $data->update();

                    $tg->command = 'showMenu';
                    $tg->update();
    
                    $this->KirimPesan($message,true);
                    $this->MyProfil();
                }
                elseif ($tg->command == 'InputEmail')
                {
                    $message ='';
                    $message .='Email <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                    $message .='<i>Silakan Masukkan nomor HP anda</i> :' . chr(10);
                    $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                    $data->email = $this->text;
                    $data->update();

                    $tg->command = 'InputHP';
                    $tg->update();
    
                    $this->KirimPesan($message,true);
                }
                elseif ($tg->command == 'EditEmail')
                {
                    $message ='';
                    $message .='Email <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                    $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                    $data->email = $this->text;
                    $data->update();

                    $tg->command = 'showMenu';
                    $tg->update();
    
                    $this->KirimPesan($message,true);
                    $this->MyProfil();
                }
                elseif ($tg->command == 'EditNoHp')
                {
                    $message ='';
                    $message .='Nomor HP <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                    $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                    $data->nohp = $this->text;
                    $data->update();

                    $tg->command = 'showMenu';
                    $tg->update();
    
                    $this->KirimPesan($message,true);
                    $this->MyProfil();
                }
                elseif ($tg->command == 'InputHP')
                {
                    $message ='';
                    $message .='Nomor HP <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                    $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                    $data->nohp = $this->text;
                    $data->update();

                    $tg->command = 'showMenu';
                    $tg->update();
    
                    $this->KirimPesan($message,true);
                    $this->showMenu();
                }
                elseif ($tg->command == 'CariPublikasi')
                {
                     //log keyword yg dicari
                     LogCari::create([
                        'username' => $this->username,
                        'chatid' => $this->chat_id,
                        'command' => 'CariPublikasi',
                        'keyword' => $this->text
                    ]);
                    //batas
                    $h = new WebApiBps();
                    $keyword = rawurlencode($this->text);
                    $response = $h->caripublikasi($keyword,1);
                    if ($response['data-availability']=='available')
                    {
                        if ($response['data'][0]['pages'] > 1) 
                        {
                            //ada lebih 1 pages
                            $total_tabel = $response['data'][0]['pages'];
                            if ($total_tabel > 3) 
                            {
                                $total_tabel = 3;
                            }
                            $message ='';
                            $message ='Hasil Pencarian Publikasi : ' . chr(10) .chr(10);
                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $respon = $h->caripublikasi($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $message .= 'Judul Publikasi : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= 'Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> | <a href="'.$item['pdf'].'">Download PDF</a> ('.$item['size'].')' .chr(10) .chr(10);
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
                                
                                $message .= 'Judul Publikasi : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= 'Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> | <a href="'.$item['pdf'].'">Download PDF</a> ('.$item['size'].')' .chr(10) .chr(10);
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
                     //log keyword yg dicari
                     LogCari::create([
                        'username' => $this->username,
                        'chatid' => $this->chat_id,
                        'command' => 'CariStatistik',
                        'keyword' => $this->text
                    ]);
                    //batas
                    $h = new WebApiBps();
                    $keyword = rawurlencode($this->text);
                    $response = $h->caristatistik($keyword,1);
                    
                    if ($response['data-availability']=='available')
                    {
                        if ($response['data'][0]['pages'] > 1) 
                        {
                            //ada lebih 1 pages
                            $total_tabel = $response['data'][0]['pages'];
                            if ($total_tabel > 3) 
                            {
                                $total_tabel = 3;
                            }
                            $message ='';
                            $message ='Hasil Pencarian <b>Tabel Statistik</b> : ' . chr(10) .chr(10);
                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $respon = $h->caristatistik($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $message .= 'Judul Tabel : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= 'Update : <b>'.\Carbon\Carbon::parse($item['updt_date'])->format('d M Y').'</b> | <a href="'.$item['excel'].'">Download Tabel</a> ('.$item['size'].')' .chr(10) .chr(10);
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
                                
                                $message .= 'Judul Tabel : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= 'Update : <b>'.\Carbon\Carbon::parse($item['updt_date'])->format('d M Y').'</b> | <a href="'.$item['excel'].'">Download Tabel</a> ('.$item['size'].')' .chr(10) .chr(10);
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
                     //log keyword yg dicari
                     LogCari::create([
                        'username' => $this->username,
                        'chatid' => $this->chat_id,
                        'command' => 'CariBrs',
                        'keyword' => $this->text
                    ]);
                    //batas
                    $h = new WebApiBps();
                    $keyword = rawurlencode($this->text);
                    $response = $h->caribrs($keyword,1);
                    
                    if ($response['data-availability']=='available')
                    {
                        if ($response['data'][0]['pages'] > 1) 
                        {
                            //ada lebih 1 pages
                            $total_tabel = $response['data'][0]['pages'];
                            if ($total_tabel > 3) 
                            {
                                $total_tabel = 3;
                            }
                            $message ='';
                            $message ='Hasil Pencarian <b>Berita Resmi Statistik</b> : ' . chr(10) .chr(10);
                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $respon = $h->caribrs($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $message .= 'Judul : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= 'Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> | <a href="'.$item['pdf'].'">Download</a> ('.$item['size'].')' .chr(10) .chr(10);
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
                                
                                $message .= 'Judul : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= 'Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> | <a href="'.$item['pdf'].'">Download</a> ('.$item['size'].')' .chr(10) .chr(10);
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
                     //log keyword yg dicari
                     LogCari::create([
                        'username' => $this->username,
                        'chatid' => $this->chat_id,
                        'command' => 'CariLainnya',
                        'keyword' => $this->text
                    ]);
                    //batas
                    $h = new WebApiBps();
                    $keyword = rawurlencode($this->text);
                    $response = $h->carilain($keyword,1);
                    
                    if ($response['data-availability']=='available')
                    {
                        if ($response['data'][0]['pages'] > 1) 
                        {
                            //ada lebih 1 pages
                            $total_tabel = $response['data'][0]['pages'];
                            if ($total_tabel > 3) 
                            {
                                $total_tabel = 3;
                            }
                            $message ='';
                            $message ='Hasil Pencarian <b>Lainnya</b> : ' . chr(10) .chr(10);
                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $respon = $h->carilain($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $message .= 'Judul : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= 'Tanggal : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b>' .chr(10) .chr(10);
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
                                $url_link = explode("-",$item['rl_date']);
                                $link = 'https://ntb.bps.go.id/news/'.$url_link[0].'/'.$url_link[1].'/'.$url_link[2].'/'.$item['news_id'].'/bpsntb.html';
                                $message .= 'Judul : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= 'Tanggal : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> | <a href="'.$link.'">Link</a>' .chr(10) .chr(10);
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
            else 
            {
                //sama sekali belum ada di log / pengunjung sudah selesai
                LogPengunjung::create([
                    'username' => $this->username,
                    'chatid' => $this->chat_id,
                    'command' => 'showMenu'
                ]);
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

    public function cariBrsSaja($keyword)
    {
        $h = new WebApiBps();
        $keyword = rawurlencode($keyword);
        $response = $h->caribrs($keyword,1);
        
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
