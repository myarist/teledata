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
    protected $message_id;
    protected $waktu_kirim;
    protected $msg_id;
    protected $forward_date;
    protected $url_photo;
    protected $hari_libur;
    protected $keyboard_bawah;

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
                    ['text'=> 'Feedback', 'callback_data'=> 'myfeedback']
                ],
                [
                    ['text'=> 'Selesai', 'callback_data'=> 'selesai']
                ]
            ]
        ];
        $this->keyboard_default_admin = [
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
                    ['text' => 'Menu Admin','callback_data' => 'menuadmin']
                ],
                [
                    ['text'=> 'Tentang Bot', 'callback_data'=> 'tentangbot']
                ],
                [
                    ['text'=> 'Feedback', 'callback_data'=> 'myfeedback']
                ],
                [
                    ['text'=> 'Selesai', 'callback_data'=> 'selesai']
                ]
                ]
        ];
        $this->keyboard_myfeedback = [
            'inline_keyboard' => [
                [
                    ['text'=> 'Berikan Feedback', 'callback_data'=>'feedbacksistem'],
                    ['text'=> 'Menu Utama', 'callback_data'=>'back_menuutama']
                ],
            ]
        ];
        $this->keyboard_feedback = [
            'inline_keyboard' => [
                [
                    ['text'=> '1', 'callback_data'=>'feedback1'],
                    ['text'=> '2', 'callback_data'=>'feedback2'],
                    ['text'=> '3', 'callback_data'=>'feedback3'],
                    ['text'=> '4', 'callback_data'=>'feedback4'],
                    ['text'=> '5', 'callback_data'=>'feedback5']
                ],
            ]
        ];
        $this->keyboard_admin = [
            'inline_keyboard' => [
                [
                    ['text'=> 'Ubah Status Online', 'callback_data'=>'flagstatusonline']
                ],
                [
                    ['text'=> 'List Pengunjung', 'callback_data'=>'logpengunjung'],
                    ['text'=> 'Log Pencarian', 'callback_data'=>'logpencarian']
                ],
                [
                    ['text'=> 'List Operator', 'callback_data'=>'listoperator'],
                    ['text'=> 'List Feedback', 'callback_data'=>'listfeedback']
                ],
                [
                    ['text'=> 'Menu Utama', 'callback_data'=>'back_menuutama']
                ]
            ]
        ];
        $this->keyboard_default_admin_belumtg = [
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
                    ['text' => 'Update Telegram ID','callback_data' => 'updateidtg']
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
                    ['text'=> 'Kembali ke Menu Utama', 'callback_data'=>'back_menuutama']
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
        $this->keyboard_kembali = [
            'inline_keyboard' => [
                [
                    ['text'=> 'Kembali ke Menu Utama', 'callback_data'=>'menuawal']
                ]
                ]
        ];
        $this->keyboard_tentang_kembali = [
            'inline_keyboard' => [
                [
                    ['text'=> 'Kembali ke Menu Utama', 'callback_data'=>'back_menuutama']
                ]
                ]
        ];
        $this->keyboard_konsul_kembali = [
            'inline_keyboard' => [
                [
                    ['text'=> 'Kembali ke Menu Utama', 'callback_data'=>'back_menuutama']
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
                    ['text'=> 'Kembali ke Menu Utama', 'callback_data'=>'back_menuutama']
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
                    ['text'=> 'Kembali ke Menu Utama', 'callback_data'=>'menuawal']
                ]
                ]
        ];
        $this->keyboard_bawah = [
                'keyboard' => [
                    ['Konsultasi','Pencarian'],
                    ['Profile', 'Tentang Saya'],
                    ['Feedback','Selesai']
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
        ];
        $r = file_get_contents("https://github.com/guangrei/Json-Indonesia-holidays/raw/master/calendar.json");
        $this->hari_libur = json_decode($r, true);
        $this->keyboard = json_encode($this->keyboard_default);
    }

    public function getMe()
    {
        $response = $this->telegram->getMe();
        //return $response;
        return view('admin.getme',['respon'=>$response]);
    }
    public function WebhookInfo()
    {
        $h = new WebApiBps();
        $response = $h->webinfo();

        //return $response;
        return view('admin.botstatus',['respon'=>$response]);
    }
    public function setWebHook()
    {
        $url = env('TELEGRAM_WEBHOOK_URL') . '/' . env('TELEGRAM_HASH_URL') . '/webhook';
        $response = $this->telegram->setWebhook(['url' => $url]);
        //dd($response);
        return view('admin.setwebhook',['respon'=>$response]);
    }
    public function OffWebHook()
    {
        $h = new WebApiBps();
        $response = $h->resetwebhook();

        //return $response;
        return view('admin.setwebhook',['respon'=>$response]);
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
            $this->message_id = $update->callbackQuery->message->message_id;
            $this->waktu_kirim = $update->callbackQuery->message->date;

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
                case 'menuadmin':
                    $this->MenuAdmin();
                    break;
                case 'flagstatusonline':
                    $this->FlagKonsultasi();
                    break;
                case 'listoperator':
                    $this->ListOperator();
                    break;
                case 'gantipasswd':
                    $this->GantiPasswd();
                    break;
                case 'myfeedback':
                    $this->MyFeedBack();
                    break;
                case 'feedbacksistem':
                    $this->FeedBackSistem();
                    break;
                case 'feedback1':
                    $this->ProsesFeedBack();
                    break;
                case 'feedback2':
                    $this->ProsesFeedBack();
                    break;
                case 'feedback3':
                    $this->ProsesFeedBack();
                    break;
                case 'feedback4':
                    $this->ProsesFeedBack();
                    break;
                case 'feedback5':
                    $this->ProsesFeedBack();
                    break;
                case 'listfeedback':
                    $this->ListFeedBack();
                    break;
                case 'back_konsul':
                    $this->BackUtama();
                    break;
                case 'back_menuutama':
                    $this->BackUtama();
                    break;
                default:
                $this->showMenu();
                    break;
            }

        }
        elseif (isset($request['channel_post']))
        {
            // !pin pesan yang di pinned
            $chan_id = $update->channel_post->sender_chat->id;
            if (strpos($update->channel_post->text,'!pin') !== false)
            {
                if (isset($request['channel_post']['reply_to_message']))
                {
                    $msg_id = $update->channel_post->reply_to_message->message_id;
                }
                else
                {
                    $msg_id = $update->channel_post->message_id;
                }
                $data = [
                    'chat_id' => $chan_id,
                    'message_id' => $msg_id,
                    'disable_notification' => true,
                ];
                $this->telegram->pinChatMessage($data);
            }
        }
        else
        {

            /*
            Pertama kali pengunjung menghubungi bot klik /start
            */
            //cek dulu apakah messagenya ada edited_message
            if (isset($request['edited_message']))
            {
                $this->chat_id = $request['edited_message']['chat']['id'];
                $this->first_name = $request['edited_message']['from']['first_name'];
                $this->text = $request['edited_message']['text'];
                $this->message_id = $request['edited_message']['message_id'];
                $this->waktu_kirim = $request['edited_message']['date'];
                if (isset($request['edited_message']['reply_to_message']['forward_date']))
                {
                    $this->forward_date = $request['edited_message']['reply_to_message']['forward_date'];
                }
                else
                {
                    $this->forward_date = $request['edited_message']['date'];
                }
                if (array_key_exists("username",$request['edited_message']['from']))
                {
                    $this->username = $request['edited_message']['from']['username'];
                }
                else
                {
                    $this->username= $this->first_name;
                }
            }
            else
            {
                $this->chat_id = $request['message']['chat']['id'];
                $this->first_name = $request['message']['from']['first_name'];
                $this->text = $request['message']['text'];
                $this->message_id = $request['message']['message_id'];
                $this->waktu_kirim = $request['message']['date'];

                if (isset($request['message']['reply_to_message']['forward_date']))
                {
                    $this->forward_date = $request['message']['reply_to_message']['forward_date'];
                }
                else
                {
                    $this->forward_date = $request['message']['date'];
                }
                if (array_key_exists("username",$request['message']['from']))
                {
                    $this->username = $request['message']['from']['username'];
                }
                else
                {
                    $this->username= $this->first_name;
                }
            }


            switch ($this->text) {
                case '/start':
                    $this->AwalStart();
                    break;
                case '/tambahadmin':
                    $this->TambahAdmin();
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
                //$this->keyboard = $this->keyboard_bawah;
                /*
                $reply_markup = $telegram->replyKeyboardMarkup([
                    'keyboard' => $this->keyboard_bawah,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]); */
                //$this->keyboard = json_encode($this->keyboard_bawah);
                $this->KirimPesan($message, true);
                $this->showMenu();
            }

        }
        else
        {
            $message = 'Selamat datang di <b>TeleDATA (Telegram Data BPSNTB)</b>' .chr(10);
            $message .= '<b>BPS Provinsi Nusa Tenggara Barat</b>' .chr(10) .chr(10);
            $message .= '<i>Untuk dapat menggunakan layanan <b>TeleData</b></i>' .chr(10);
            $message .= '<i>Anda perlu memasukkan <b>Nama Lengkap</b>, <b>Email</b> dan <b>No HP</b></i>' .chr(10) .chr(10);
            $this->nama = $this->username;
            $data = new DataPengunjung();
            $data->username = $this->username;
            $data->chatid = $this->chat_id;
            $data->save();

            $this->KirimPesan($message,true);
            $this->InputNama();
        }

    }
    public function TambahAdmin()
    {
        $cek_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->count();
        if ($cek_admin > 0)
        {
            //bisa akses menu ini
            $message = "Masukkan tambah admin sesuai format berikut : ";
            $message = "perintah : <b>/tambahadmin<spasi>namalengkap-username_telegram-email-password</b>";

            LogPengunjung::create([
                'username' => $this->username,
                'chatid' => $this->chat_id,
                'command' => __FUNCTION__,
                'msg_id' => $this->message_id
            ]);
            $this->keyboard = json_encode($this->keyboard_edit_profil);

        }
        else
        {
            //tidak bisa akses menu ini
            $message = "Anda tidak dapat mengakses menu ini: ";
            $this->keyboard = json_encode($this->keyboard_edit_profil);
        }

        $this->KirimPesan($message,true,true);

    }
    public function BackKonsul()
    {
        $this->showMenu();
        $this->HapusKeyboard($this->chat_id);
        //$this->message_id = $this->message_id - 1;
        //$this->HapusKeyboard($this->chat_id);
    }
    public function BackUtama()
    {
        $this->showMenu();
        $this->HapusKeyboard($this->chat_id);
        //$this->message_id = $this->message_id - 1;
        //$this->HapusKeyboard($this->chat_id);
    }
    public function ListFeedBack()
    {
        $data = LogFeedback::orderBY('created_at','desc')->take(30)->get();
        $message = 'Data 30 Feedback Pengunjung <b>TeleDATA</b> terakhir' .chr(10);
        $i=1;
        foreach ($data as $item) {
            if ($item->nilai_feedback == '5')
            {
                $nilai = '⭐️⭐️⭐️⭐️⭐️';
            }
            elseif ($item->nilai_feedback == '4')
            {
                $nilai = '⭐️⭐️⭐️⭐️';
            }
            elseif ($item->nilai_feedback == '3')
            {
                $nilai = '⭐️⭐️⭐️';
            }
            elseif ($item->nilai_feedback == '2')
            {
                $nilai = '⭐️⭐️';
            }
            else
            {
                $nilai = '⭐️';
            }
            $message .= $i.'. Nama: <b>'.$item->Pengunjung->nama .'</b> | chat_id: <b>'.$item->chatid.'</b> | username: <b>'.$item->username.'</b>'.chr(10). $nilai .' <i>'.Carbon::parse($item->created_at)->format('d M Y H:i').'</i>'. chr(10) .'<i>'.$item->isi_feedback.'</i> ' .chr(10) .chr(10);
            $i++;
            if ($i==11 or $i==21)
            {
                $this->KirimPesan($message,true);
                $message = '';
            }
        }
        $this->keyboard = json_encode($this->keyboard_admin);
        $this->KirimPesan($message,true,true);
       //$this->MenuAdmin();
    }
    public function MyFeedBack($hapus=false)
    {
        //cek dulu feedbacknya
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => 'FeedBackSistem',
            'msg_id' => $this->message_id
        ]);
        $message = 'Penilaian terhadap <b>TeleDATA</b>' .chr(10) .chr(10);
        $cek_fb = LogFeedback::where('chatid',$this->chat_id)->count();
        if ($cek_fb > 0)
        {
            //sudah ada ngisi
            $data = LogFeedback::where('chatid',$this->chat_id)->first();
            if ($data->nilai_feedback == '5')
            {
                $nilai = '⭐️⭐️⭐️⭐️⭐️';
            }
            elseif ($data->nilai_feedback == '4')
            {
                $nilai = '⭐️⭐️⭐️⭐️';
            }
            elseif ($data->nilai_feedback == '3')
            {
                $nilai = '⭐️⭐️⭐️';
            }
            elseif ($data->nilai_feedback == '2')
            {
                $nilai = '⭐️⭐️';
            }
            else
            {
                $nilai = '⭐️';
            }

            $message .= '<b>Nilai</b> : '. $nilai .chr(10);
            $message .= '<b>Komentar</b> : <i>'.$data->isi_feedback.'</i>' .chr(10);
            $message .= '<b>Tanggal</b> : '.\Carbon\Carbon::parse($data->updated_at)->format('j F Y H:i') .chr(10);
        }
        else
        {

            $message .= '<i>Belum ada feedback dari Bapak/Ibu</i>' .chr(10);
        }

        $this->keyboard = json_encode($this->keyboard_myfeedback);
        $this->KirimPesan($message,true,true);
        if (!$hapus)
        {
            $this->HapusKeyboard($this->chat_id);
        }

    }
    public function FeedBackSistem($hapus=false)
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message ='';
        $message .= 'Bagaimana penilaian Bapak/Ibu untuk TeleData' .chr(10);
        $message .= 'Silakan pilih nilai dibawah ini : ' .chr(10);
        $this->keyboard = json_encode($this->keyboard_feedback);
        $this->KirimPesan($message,true,true);
        if (!$hapus)
        {
            $this->HapusKeyboard($this->chat_id);
        }
    }
    public function ProsesFeedBack($hapus=false)
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => 'FeedBackSistem',
            'msg_id' => $this->message_id
        ]);
        //input nilai sesuai pilihan
        //cek dulu apakah sudah pernah ngisi
        //kalo sudah pernah mengisi langsung update saja
        if ($this->text == 'feedback1')
        {
            $nilai = 1;
        }
        elseif ($this->text == 'feedback2')
        {
            $nilai = 2;
        }
        elseif ($this->text == 'feedback3')
        {
            $nilai = 3;
        }
        elseif ($this->text == 'feedback4')
        {
            $nilai = 4;
        }
        else
        {
            $nilai = 5;
        }

        $cek_fb = LogFeedback::where('chatid',$this->chat_id)->count();
        if ($cek_fb > 0)
        {
            //sudah pernah mengisi feedback
            $data = LogFeedback::where('chatid',$this->chat_id)->first();
            $data->username = $this->username;
            $data->nilai_feedback = $nilai;
            $data->msg_id = $this->message_id;
            $data->waktu_kirim = $this->waktu_kirim;
            $data->update();
        }
        else
        {
            //baru ngisi feedback
            $data = new LogFeedback();
            $data->username = $this->username;
            $data->chatid = $this->chat_id;;
            $data->nilai_feedback = $nilai;
            $data->msg_id = $this->message_id;
            $data->waktu_kirim = $this->waktu_kirim;
            $data->save();
        }
        $message ='';
        $message .= 'Terimakasih atas penilaian Bapak/Ibu untuk perbaikan <b>Teledata</b> Kedepan' .chr(10) .chr(10);
        $message .= 'Silakan masukkan komentar Bapak/Ibu tentang <b>TeleDATA</b> : '.chr(10) .chr(10);
        $this->KirimPesan($message,true);
        if (!$hapus)
        {
            $this->HapusKeyboard($this->chat_id);
        }
    }

    public function showMenu($info = false)
    {

        $message = 'Selamat datang di <b>TeleDATA (Telegram Data BPSNTB)</b>' .chr(10);
        $message .= '<b>BPS Provinsi Nusa Tenggara Barat</b>' .chr(10) .chr(10);
        $message .= 'Silakan <b>Pilih Layanan</b> yang tersedia : ' .chr(10);
        $cek_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->count();
        if ($cek_admin > 0)
        {
            //admin dan tampilkan keyboard
            $data_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->first();
            $message .= chr(10).'Role : Admin Sistem <b>TeleData</b> ('.$this->username.')' .chr(10);
            if ($data_admin->chatid_tg == '')
            {
                //update chatid_tg dulu
                $this->keyboard = json_encode($this->keyboard_default_admin_belumtg);
            }
            else
            {
                //langsung tampilkan menuadmin
                $this->keyboard = json_encode($this->keyboard_default_admin);
            }
            //jika chatid_tg belum ada isinya di menu admin
            //update keyboard  $this->keyboard_default_admin_belumtg

        }
        else
        {
            //keyboard biasa
            $this->keyboard = json_encode($this->keyboard_default);

        }
        $this->KirimPesan($message,true,true);

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => 'showMenu',
            'msg_id'=> $this->message_id
        ]);

    }
    //nama lengkap, email, nomor hp
    public function InputEmail()
    {
        $message = "<i>Silakan masukkan alamat email anda : </i>";
        $this->KirimPesan($message,true);
    }
    public function EditEmail()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message = "<i>Silakan masukkan alamat <b>email baru</b> anda : </i>";
        $this->KirimPesan($message,true);
    }
    public function InputHP()
    {
        $message = "<i>Silakan Masukkan Nomor HP anda</i> : ";
        $this->KirimPesan($message,true);
    }
    public function Selesai()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message = "<b>Terimakasih Telah Menggunakan Layanan Kami</b>" .chr(10);
        $this->KirimPesan($message,true);
        $this->HapusKeyboard($this->chat_id);
        $count = LogPengunjung::where('chatid', $this->chat_id)->count();
        if ($count > 0)
        {
            LogPengunjung::where('chatid', $this->chat_id)->delete();
        }
    }
    public function EditNoHp()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message = "<i>Silakan masukkan Nomor HP baru anda</i> : ";
        $this->KirimPesan($message,true);
    }
    public function InputNama()
    {
        $message = "<i>Silakan Masukkan Nama Lengkap</i> :";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);

        $this->KirimPesan($message,true);
    }
    public function EditNama()
    {
        $message = "<i>Silakan masukkan Nama Lengkap Anda</i> :";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
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
                LogPengunjung::create([
                    'username' => $this->username,
                    'chatid' => $this->chat_id,
                    'command' => __FUNCTION__,
                    'msg_id' => $this->message_id
                ]);
                $message = 'Anda terdaftar sebagai : ' .chr(10);
                $message .= 'Nama : <b>'.$data->nama.'</b>' .chr(10);
                $message .= 'Email : <b>'.$data->email.'</b>' .chr(10);
                $message .= 'No HP : <b>'.$data->nohp.'</b>' .chr(10);
                $cek_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->count();
                if ($cek_admin > 0)
                {
                    //admin
                    $message .= chr(10).'Role : Admin Sistem <b>TeleData</b> ('.$this->username.')' .chr(10);
                }
                $this->keyboard = json_encode($this->keyboard_edit_profil);
                $this->KirimPesan($message,true,true);
                $this->HapusKeyboard($this->chat_id);
            }
        }
        else
        {
            $this->AwalStart();
        }
    }
    public function MenuCari()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message = 'Silakan pilih menu <b>Pencarian Data</b> dibawah ini ' .chr(10);
        $this->keyboard = json_encode($this->keyboard_cari);
        $this->KirimPesan($message,true,true);
        $this->HapusKeyboard($this->chat_id);
    }
    public function CariPublikasi($hapus = false)
    {
        $message = "Masukkan <b>Kata Kunci </b>untuk <b>Pencarian Publikasi</b> : ";
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        if ($hapus == false)
        {
            $this->HapusKeyboard($this->chat_id);
        }
    }

    public function CariBrs($hapus = false)
    {
        $message = "Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Berita Resmi Statistik</b> : ";
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        if (!$hapus)
        {
            $this->HapusKeyboard($this->chat_id);
        }
    }

    public function MenuKonsultasi($reply = false,$hapus=false)
    {
        $message = '<b>Layanan Konsultasi Online</b>' .chr(10);
        $message .= 'Hari Layanan :  Senin - Jumat (Kecuali hari libur)' .chr(10);
        $message .= 'Jam Layanan : 08.00 - 15.00 WITA' .chr(10) .chr(10);

        //$this->KirimPesan($message,true);
        //cek dulu hari apa
        if (Carbon::now()->format('w') > 0 and Carbon::now()->format('w') < 6)
        {
            //hari kerja
            //cek jam
            //cek dulu hari libur apa ngga
            $cek_libur = isset($this->hari_libur[Carbon::now()->format("Ymd")])?true:false;
            if ($cek_libur == true)
            {
                //diluar jam layanan
                $message .= '<b>Hari Libur : '.$this->hari_libur[Carbon::now()->format("Ymd")]['deskripsi'].'</b>' .chr(10);
                $message .= '<b>Silakan tinggalkan pesan</b>' .chr(10);
                $message .= 'Pesan anda akan terbaca saat operator Online ' .chr(10) .chr(10);
                $message .= '<i>Masukkan pertanyaan untuk operator</i> : ' .chr(10);
            }
            else
            {
                //tidak libur
                //hari kerja
                if (Carbon::now()->format('H') > 7 and Carbon::now()->format('H') < 16 )
                {
                    //cek operator ada online ngga
                    if (Carbon::now()->format('H') < 15)
                    {
                        $cek_admin = User::where([['status_online','1'],['aktif','1']])->count();
                        if ($cek_admin > 0)
                        {
                            //operator ada online
                            $message .= '<b>Operator Online</b>' .chr(10) .chr(10);
                            $message .= '<i>Masukkan pertanyaan untuk operator</i> : ' .chr(10);
                        }
                        else
                        {
                            $message .= '<b>Belum ada Operator Online</b>' .chr(10);
                            $message .= 'Pesan anda akan terbaca saat operator Online ' .chr(10) .chr(10);
                            $message .= '<i>Masukkan pertanyaan untuk operator</i> : ' .chr(10);
                        }
                    }
                    else
                    {
                        //sudah jam 3 sore dan tutup
                        $message .= '<b>Diluar jam layanan</b>' .chr(10);
                        $message .= '<b>Silakan tinggalkan pesan</b>' .chr(10);
                        $message .= 'Pesan anda akan terbaca saat operator Online ' .chr(10) .chr(10);
                        $message .= '<i>Masukkan pertanyaan untuk operator</i> : ' .chr(10);
                    }

                }
                else
                {
                    //diluar jam layanan
                    $message .= '<b>Diluar jam layanan</b>' .chr(10);
                    $message .= '<b>Silakan tinggalkan pesan</b>' .chr(10);
                    $message .= 'Pesan anda akan terbaca saat operator Online ' .chr(10) .chr(10);
                    $message .= '<i>Masukkan pertanyaan untuk operator</i> : ' .chr(10);
                }
            }
        }
        else
        {
            //hari sabtu dan minggu
            $message .= '<b>Diluar hari dan jam layanan</b>' .chr(10);
            $message .= '<b>Silakan tinggalkan pesan</b>' .chr(10);
            $message .= 'Pesan anda akan terbaca saat operator Online ' .chr(10) .chr(10);
            $message .= '<i>Masukkan pertanyaan untuk operator</i> : ' .chr(10);

        }

        if ($reply)
        {
            //ReplyByAdmin
            LogPengunjung::create([
                'username' => $this->username,
                'chatid' => $this->chat_id,
                'command' => 'ReplyByAdmin',
                'msg_id' => $this->message_id
            ]);
        }
        else
        {
            LogPengunjung::create([
                'username' => $this->username,
                'chatid' => $this->chat_id,
                'command' => __FUNCTION__,
                'msg_id' => $this->message_id
            ]);
        }
        $this->keyboard = json_encode($this->keyboard_konsul_kembali);
        $this->KirimPesan($message,true,true);

        if (!$hapus)
        {
            $this->HapusKeyboard($this->chat_id);
        }

    }
    public function CariStatistik($hapus=false)
    {
        $message = "Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Statistik</b> : ";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        if ($hapus == false)
        {
            $this->HapusKeyboard($this->chat_id);
        }
    }

    public function CariLainnya($hapus=false)
    {
        $message = "Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Lainnya</b> : ";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $this->keyboard = json_encode($this->keyboard_cari_kembali);
        $this->KirimPesan($message,true,true);
        if ($hapus == false)
        {
            $this->HapusKeyboard($this->chat_id);
        }
    }

    public function TentangBot()
    {
        /*
        Bot TeleData ini merupakan inovasi dari BPS Provinsi Nusa Tenggara Barat.
Aplikasi ini dibuat untuk memudahkan pengguna data dalam melakukan pencarian data yang ada di website BPS Prov. NTB melalui Telegram.
Aplikasi ini dikembangkan oleh Bidang IPDS BPS Prov. NTB.

        */
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message ='';
        $message = '<b>TENTANG BOT TeleDATA (Telegram Data BPSNTB)</b>' .chr(10) .chr(10);
        $message .= 'Bot TeleData ini merupakan invoasi dari BPS Provinsi Nusa Tenggara Barat.' .chr(10);
        $message .= 'Aplikasi ini dibuat untuk memudahkan pengguna data dalam melakukan pencarian data yang ada di website BPS Prov. NTB melalui Telegram.' .chr(10);
        $message .= 'Aplikasi ini dikembangkan oleh Bidang IPDS BPS Prov. NTB' .chr(10);
        //$this->KirimPesan($message,true);
        $this->keyboard = json_encode($this->keyboard_tentang_kembali);
        //KirimPhoto($url,false,true);
        $this->KirimPhoto('kirimphoto',false,true);
        $this->HapusKeyboard($this->chat_id);
        //$this->showMenu();
    }
    public function LogDataPencarian($hapus=false)
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $cek = LogCari::count();
        if ($cek > 0)
        {
            $data = LogCari::orderBy('created_at','desc')->take(30)->get();
            $message = 'Data 30 Keyword Pencarian terakhir di TeleData' .chr(10);
            $i=1;
            foreach ($data as $item) {
                $message .= $i.'. Nama: <b>'.$item->username .'</b> | Keyword: ('.$item->command.') <b>'. $item->keyword .'</b> | tanggal: '. Carbon::parse($item->created_at)->format('d M Y H:i') .chr(10);
                $i++;
            }
            if (strlen($message) > 4096)
            {
                $message .= 'Pesan terlalu panjang' .chr(10);
            }
        }
        else
        {
            $message = 'Data Log Pencarian masih kosong' .chr(10);
        }

        $this->keyboard = json_encode($this->keyboard_admin);
        $this->KirimPesan($message,true,true);
       //$this->MenuAdmin();
       if (!$hapus)
       {
           $this->HapusKeyboard($this->chat_id);
       }
    }
    public function LogDataPengunjung()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $data = DataPengunjung::orderBY('created_at','desc')->take(30)->get();
        $message = 'Data 30 Pengunjung TeleData terakhir' .chr(10);
        $i=1;
        foreach ($data as $item) {
            $message .= $i.'. Nama: <b>'.$item->nama .'</b> | Email: <b>'. $item->email .'</b> | No HP: <b>'.$item->nohp.'</b> | chat_id: <b>'.$item->chatid.'</b> | username: <b>'.$item->username.'</b> | Daftar: <b>'. Carbon::parse($item->created_at)->format('d M Y H:i') .'</b>' .chr(10) .chr(10);
            $i++;
            if ($i==11 or $i==21)
            {
                $this->KirimPesan($message,true);
                $message = '';
            }
        }
        $this->keyboard = json_encode($this->keyboard_admin);
        $this->KirimPesan($message,true,true);
       //$this->MenuAdmin();
    }
    public function UpdateIdTele()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
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
        $this->MenuAdmin();
    }
    public function FlagKonsultasi()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $cek_dulu = User::where('chatid_tg','=',$this->chat_id)->count();
        if ($cek_dulu > 0)
        {
            $data_admin = User::where('chatid_tg','=',$this->chat_id)->first();
            if ($data_admin->status_online == 1)
            {
                $flag_konsultasi = 0;
            }
            else
            {
                $flag_konsultasi = 1;
            }
            $data_admin->status_online = $flag_konsultasi;
            $data_admin->update();
            $message = 'Status Online berhasil diubah' .chr(10) .chr(10);
            $this->KirimPesan($message,true);
            $this->MenuAdmin();

        }
        else
        {
           //bukan admin
           $message ='Anda bukan admin sistem'.chr(10);
           $this->KirimPesan($message,true);
           $this->MenuAwal();
        }
    }

    public function ListOperator($hapus=false)
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $cek_dulu = User::where('chatid_tg','=',$this->chat_id)->count();
        if ($cek_dulu > 0)
        {
            $data = User::orderBY('created_at','desc')->take(30)->get();
            $message = 'Data 30 Operator TeleData terakhir' .chr(10);
            $i=1;
            foreach ($data as $item) {
                if ($item->lastip != '')
                {
                    $lastlogin = $item->lastip .' ('. Carbon::parse($item->lastlogin)->format('d M Y H:i') .')';
                }
                else
                {
                    $lastlogin ='';
                }
                $message .= $i.'. Nama: <b>'.$item->nama .'</b> | Email: <b>'. $item->email .'</b> | user_tg: <b>'.$item->user_tg.'</b> | chat_id: <b>'.$item->chatid_tg.'</b> | username: <b>'.$item->username.'</b> | status_online: '.$item->status_online.' | lastlogin: <b>'. $lastlogin .'</b>' .chr(10) .chr(10);
                $i++;
                if ($i==10 or $i==20)
                {
                    $this->KirimPesan($message,true);
                    $message = '';
                }
            }
            $this->keyboard = json_encode($this->keyboard_admin);
            $this->KirimPesan($message,true,true);

        }
        else
        {
           //bukan admin
           $message ='Anda bukan admin sistem'.chr(10);
           $this->KirimPesan($message,true);
           $this->MenuAwal();
           if (!$hapus)
           {
               $this->HapusKeyboard($this->chat_id);
           }
        }
    }
    public function GantiPasswd()
    {
        $cek_dulu = User::where('chatid_tg','=',$this->chat_id)->count();
        if ($cek_dulu > 0)
        {

            $message = 'Masih dalam pengembangan' .chr(10);

            //$this->keyboard = json_encode($this->keyboard_admin);
            $this->KirimPesan($message,true);
            $this->MenuAdmin();
        }
        else
        {
           //bukan admin
           $message ='Anda bukan admin sistem'.chr(10);
           $this->KirimPesan($message,true);
           $this->MenuAwal();
        }
    }
    public function MenuAdmin($hapus=false)
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $cek_dulu = User::where('chatid_tg','=',$this->chat_id)->count();
        if ($cek_dulu > 0)
        {
            $data_admin = User::where('chatid_tg','=',$this->chat_id)->first();
            if ($data_admin->status_online == 1)
            {
                $flag_statusonline = 'ONLINE';
            }
            else
            {
                $flag_statusonline = 'OFFLINE';
            }
            if ($data_admin->aktif == 1)
            {
                $flag_aktif = 'AKTIF';
            }
            else
            {
                $flag_aktif = 'NONAKTIF';
            }
            if ($data_admin->lastip != '')
            {
                $lastlogin = $data_admin->lastip .' ('. Carbon::parse($data_admin->lastlogin)->format('d M Y H:i') .')';
            }
            else
            {
                $lastlogin ='';
            }
            $message = '<b>Menu Admin TeleData</b>' .chr(10) .chr(10);
            $message .= 'Nama : <b>'.$data_admin->nama.'</b>' .chr(10);
            $message .= 'Username (Web) : <b>'.$data_admin->username.'</b>' .chr(10);
            $message .= 'Username (TG) : <b>'.$data_admin->user_tg.'</b>' .chr(10);
            $message .= 'chatid (TG) : <b>'.$data_admin->chatid_tg.'</b>' .chr(10);
            $message .= 'Email : <b>'.$data_admin->email.'</b>' .chr(10);
            $message .= 'Lastlogin : <b>'.$lastlogin.'</b>' .chr(10);
            $message .= 'Status Akun : <b>'.$flag_aktif.'</b>' .chr(10);
            $message .= 'Status Online : <b>'.$flag_statusonline.'</b>' .chr(10);
            if ($data_admin->status_online == 1)
            {
                $flag_konsultasi = 'Flag Konsultasi ONLINE';
            }
            else
            {
                $flag_konsultasi = 'Flag Konsultasi OFFLINE';
            }

            $this->keyboard = json_encode($this->keyboard_admin);
            $this->KirimPesan($message,true,true);
            if (!$hapus)
            {
                $this->HapusKeyboard($this->chat_id);
            }
        }
        else
        {
            //bukan admin
            $message ='Anda bukan admin sistem'.chr(10);
            $this->KirimPesan($message,true);
            if (!$hapus)
            {
                $this->HapusKeyboard($this->chat_id);
            }
            $this->MenuAwal();
        }
    }
    public function CheckInputan()
    {
            $cek = LogPengunjung::where('chatid','=',$this->chat_id)->count();
            if ($cek > 0 )
            {
                $tg = LogPengunjung::where('chatid','=',$this->chat_id)->latest("updated_at")->first();
                if ($tg->command == 'InputNama') {

                    $pesan_error = [
                        'required' => ':attribute wajib terisi!!!',
                        'string'=> ':attribute harus berupa karakter',
                        'regex'=> ':attribute harus berupa karakter',
                        'min' => ':attribute harus diisi minimal :min karakter!!!',
                        'max' => ':attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Nama' => $this->text],
                            ['Nama' => 'string|min:3|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $this->KirimPesan($message,true);
                        $this->InputNama();
                    }
                    else
                    {
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

                }
                elseif ($tg->command == 'EditNama') {
                    $pesan_error = [
                        'required' => ':attribute wajib terisi!!!',
                        'string'=> ':attribute harus berupa karakter',
                        'regex'=> ':attribute harus berupa karakter',
                        'min' => ':attribute harus diisi minimal :min karakter!!!',
                        'max' => ':attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Nama' => $this->text],
                            ['Nama' => 'string|min:3|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $this->KirimPesan($message,true);
                        $this->EditNama();
                    }
                    else
                    {
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
                }
                elseif ($tg->command == 'InputEmail')
                {
                    $pesan_error = [
                        'required' => ':attribute wajib terisi!!!',
                        'email'=> ':attribute harus alamat lengkap',
                        'regex'=> ':attribute harus berupa alamat yang valid',
                        'min' => ':attribute harus diisi minimal :min karakter!!!',
                        'max' => ':attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Email' => $this->text],
                            ['Email' => 'required|email|regex:/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $this->KirimPesan($message,true);
                        $this->InputEmail();
                    }
                    else
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

                }
                elseif ($tg->command == 'EditEmail')
                {
                    $pesan_error = [
                        'required' => ':attribute wajib terisi!!!',
                        'email'=> ':attribute harus alamat lengkap',
                        'regex'=> ':attribute harus berupa alamat yang valid',
                        'min' => ':attribute harus diisi minimal :min karakter!!!',
                        'max' => ':attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Email' => $this->text],
                            ['Email' => 'required|email|regex:/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $this->KirimPesan($message,true);
                        $this->EditEmail();
                    }
                    else
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

                }
                elseif ($tg->command == 'EditNoHp')
                {
                    $pesan_error = [
                        'required' => ':attribute wajib terisi!!!',
                        'regex'=> ':attribute harus berupa angka',
                        'min' => ':attribute harus diisi minimal :min angka!!!',
                        'max' => ':attribute harus diisi maksimal :max angka!!!',
                    ];
                    $validator = Validator::make(['Nohp' => $this->text],
                            ['Nohp' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:13'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $this->KirimPesan($message,true);
                        $this->EditNoHp();
                    }
                    else
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
                }
                elseif ($tg->command == 'InputHP')
                {
                    $pesan_error = [
                        'required' => ':attribute wajib terisi!!!',
                        'regex'=> ':attribute harus berupa angka',
                        'min' => ':attribute harus diisi minimal :min angka!!!',
                        'max' => ':attribute harus diisi maksimal :max angka!!!',
                    ];
                    $validator = Validator::make(['Nohp' => $this->text],
                            ['Nohp' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:13'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $this->KirimPesan($message,true);
                        $this->InputHP();
                    }
                    else
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
                            $message ='Hasil Pencarian Publikasi dengan kata kunci <b>'.$this->text.'</b> :  ' . chr(10) .chr(10);
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
                            $this->KirimPesan($message,true);
                        }
                        else
                        {
                            $message ='';
                            $message ='Hasil Pencarian Publikasi dengan kata kunci <b>'.$this->text.'</b> :  ' . chr(10) .chr(10);

                            foreach ($response['data'][1] as $item)
                            {

                                $message .= 'Judul Publikasi : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= 'Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> | <a href="'.$item['pdf'].'">Download PDF</a> ('.$item['size'].')' .chr(10) .chr(10);
                            }
                            $this->keyboard = json_encode($this->keyboard_cari_kembali);
                            $this->KirimPesan($message,true);
                        }

                    }
                    else
                    {
                        $message ='Publikasi yang anda cari tidak tersedia' .chr(10);
                        $message .= 'Ulangi pencarian publikasi' .chr(10);
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        //$this->KirimPesan($message,true,true);
                        $this->KirimPesan($message,true);
                    }

                    //$tg->command = 'showMenu';
                    //$tg->update();
                    $this->CariPublikasi(true);

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
                            $this->KirimPesan($message,true);
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
                            $this->KirimPesan($message,true);
                        }

                    }
                    else
                    {
                        $message ='<b>Tabel Statistik</b> yang anda cari tidak tersedia' .chr(10);
                        $message .= 'Ulangi pencarian tabel statistik' .chr(10);
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true);
                    }

                    $this->CariStatistik(true);
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
                            $this->KirimPesan($message,true);
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
                            $this->KirimPesan($message,true);
                        }

                    }
                    else
                    {
                        $message ='<b>Pencarian Berita Resmi Statistik</b> yang anda cari tidak tersedia' .chr(10);
                        $message .= 'Ulangi pencarian lainnya' .chr(10);
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true);
                    }
                    $this->CariBrs(true);
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
                            $this->KirimPesan($message,true);
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
                            $this->KirimPesan($message,true);
                        }

                    }
                    else
                    {
                        $message ='<b>Pencarian Lainnya</b> yang anda cari tidak tersedia' .chr(10);
                        $message .= 'Ulangi pencarian lainnya' .chr(10);
                        $this->keyboard = json_encode($this->keyboard_cari_kembali);
                        $this->KirimPesan($message,true);
                    }
                    $this->CariLainnya(true);
                }
                elseif ($tg->command == 'ReplyByAdmin')
                {
                    //ambil dulu pesan sebelumnya utk dptkan chat_id
                    //reply_to_message harus isset
                    //$this->first_name = $request['message']['from']['first_name'];
                    //ambil forward_date dan text
                    //cocokan dgn di log_pesan

                    $cek_pesan = LogPesan::where('waktu_kirim','=',$this->forward_date)->count();
                    if ($cek_pesan > 0)
                    {
                        $dt = LogPesan::where('waktu_kirim','=',$this->forward_date)->first();
                        $d_pengunjung = DataPengunjung::where('chatid',$dt->chatid)->first();
                        $data_admin = User::where('chatid_tg',$this->chat_id)->first();
                        if ($data_admin)
                        {
                            $nama_admin = $data_admin->user_tg;
                        }
                        else
                        {
                            $nama_admin='admin';
                        }
                        $this->msg_id = $dt->msg_id;
                        $pesan = $this->text .' -'.$nama_admin;
                        //save replynya
                        $data_baru = new LogPesan();
                        $data_baru->username = 'admin';
                        $data_baru->chatid = '1';
                        $data_baru->isi_pesan = $pesan;
                        $data_baru->msg_id = $this->message_id;
                        $data_baru->waktu_kirim = $this->waktu_kirim;
                        $data_baru->chatid_penerima = $dt->chatid;
                        $data_baru->chat_admin = '1';
                        $data_baru->save();

                        $this->KirimByAdmin($pesan,$dt->chatid, true);
                        //$this->MenuKonsultasi(true,true);
                        //pesan diterukan ke admin yg balas

                        $message ='';
                        $message .='Pesan anda <b>'.$pesan.'</b> sudah terkirim ke <b>'.$d_pengunjung->nama.' ('.$d_pengunjung->username.')</b>' .chr(10) .chr(10);

                        $this->KirimPesan($message,true);
                        //forward ke admin yg lain jg
                        //pilih forward ke admin lain
                        $message_ke_admin_lain ='';
                        $message_ke_admin_lain.='Pesan dari admin ('.$data_admin->nama.') :  <b>'.$pesan.'</b> sudah terkirim ke <b> '.$d_pengunjung->nama.' ('.$d_pengunjung->username.')</b>' .chr(10) .chr(10);

                        $cek_admin_online = User::where([['chatid_tg','<>',''],['status_online','=','1']])->count();
                        if ($cek_admin_online > 0)
                        {
                            //kirim forward pesan
                            $dataadmin = User::where([['chatid_tg','<>',''],['status_online','=','1']])->get();
                            foreach ($dataadmin as $item) {
                                $this->chat_id = $item->chatid_tg;
                                if ($item->chatid_tg != $data_admin->chatid_tg)
                                {
                                    $this->KirimPesan($message_ke_admin_lain,true);
                                }

                                //$this->TeruskanPesan($item->chatid_tg);
                                /*
                                LogPengunjung::create([
                                    'username' => $item->user_tg,
                                    'chatid' => $item->chatid_tg,
                                    'command' => 'ReplyByAdmin',
                                    'msg_id' => $this->message_id
                                ]);
                                */
                            }

                        }

                    }
                    else
                    {

                        //kembali ke menukonsultasi
                        $message ='';
                        $message .='Pesan anda <b>'.$this->text.'</b> belum terkirim' . chr(10) .chr(10);
                        $message .= 'Gunakan fitur reply untuk membalas pesan ke pengunjung' .chr(10).chr(10);

                        $this->KirimPesan($message,true);
                        //$this->MenuKonsultasi(true,true);
                    }
                }
                elseif ($tg->command == 'FeedBackSistem')
                {
                    $cek_fb = LogFeedback::where('chatid',$this->chat_id)->count();
                    if ($cek_fb > 0)
                    {
                        //sudah pernah mengisi feedback
                        $data = LogFeedback::where('chatid',$this->chat_id)->first();
                        $data->username = $this->username;
                        $data->isi_feedback = $this->text;
                        $data->msg_id = $this->message_id;
                        $data->waktu_kirim = $this->waktu_kirim;
                        $data->update();
                    }
                    else
                    {
                        //baru ngisi feedback
                        $data = new LogFeedback();
                        $data->username = $this->username;
                        $data->chatid = $this->chat_id;;
                        $data->isi_feedback = $this->text;
                        $data->msg_id = $this->message_id;
                        $data->waktu_kirim = $this->waktu_kirim;
                        $data->save();
                    }
                    $message ='';
                    //$message .='Masukkan Bapak/Ibu <b>'.$this->text.'</b> sudah tersimpan' . chr(10);
                    $message .='Masukkan Bapak/Ibu sudah tersimpan' . chr(10);
                    $message .= 'Terimakasih atas masukkan Bapak/Ibu untuk perbaikan <b>TeleDATA</b>' .chr(10).chr(10);
                    $this->msg_id = $this->message_id;
                    $this->ReplyPesan($message,true);
                    $this->MyFeedBack(true);
                }
                elseif ($tg->command == 'MenuKonsultasi')
                {
                    $dt = new LogPesan();
                    $dt->username = $this->username;
                    $dt->chatid = $this->chat_id;
                    $dt->isi_pesan = $this->text;
                    $dt->msg_id = $this->message_id;
                    $dt->waktu_kirim = $this->waktu_kirim;
                    $dt->save();
                    //cek admin yg online dan ada chatid langsung forwardkan
                    $cek_admin_online = User::where([['chatid_tg','<>',''],['status_online','=','1']])->count();
                    if ($cek_admin_online > 0)
                    {
                        //kirim forward pesan
                        $data_admin = User::where([['chatid_tg','<>',''],['status_online','=','1']])->get();
                        foreach ($data_admin as $item) {
                            $this->TeruskanPesan($item->chatid_tg);
                            LogPengunjung::create([
                                'username' => $item->user_tg,
                                'chatid' => $item->chatid_tg,
                                'command' => 'ReplyByAdmin',
                                'msg_id' => $this->message_id
                            ]);
                        }

                    }
                        $message ='';
                        $message .='Pesan anda <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);

                        /*
                        $tg->command = 'showMenu';
                        $tg->update();
                        */
                        $this->KirimPesan($message,true);
                        $this->MenuKonsultasi(false,true);
                }
                else
                {
                    $message ='';
                    $message .='Perintah tidak dikenali. <b>Silakan pilih menu dibawah ini</b>' . chr(10) .chr(10);
                    $tg->command = 'showMenu';
                    $tg->update();
                    $this->KirimPesan($message,true);
                    $this->AwalStart();
                }
            }
            else
            {
                //sama sekali belum ada di log / pengunjung sudah selesai
                LogPengunjung::create([
                    'username' => $this->username,
                    'chatid' => $this->chat_id,
                    'command' => 'showMenu',
                    'msg_id'=> $this->message_id
                ]);
                $this->AwalStart();
            }
    }
    protected function HapusKeyboard($chat_id)
    {
        $cek = LogPengunjung::where('chatid','=',$chat_id)->count();
        if ($cek > 0 )
        {
            $tg = LogPengunjung::where('chatid','=',$chat_id)->latest("updated_at")->first();
            $data = [
                'chat_id' => $chat_id,
                'message_id' => $tg->msg_id,
            ];
            $this->telegram->deleteMessage($data);
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
    protected function ReplyPesan($message, $parse_html = false, $keyboard = false)
    {
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $message,
            'disable_web_page_preview'=> true,
            'reply_to_message_id' => $this->msg_id
        ];
        if ($parse_html) $data['parse_mode'] = 'HTML';
        if ($keyboard) $data['reply_markup'] = $this->keyboard;

        $this->telegram->sendMessage($data);
    }
    protected function KirimByAdmin($message,$chatid, $parse_html = false, $keyboard = false)
    {
        $data = [
            'chat_id' => $chatid,
            'text' => $message,
            'disable_web_page_preview'=> true,
            'reply_to_message_id' => $this->msg_id
        ];
        if ($parse_html) $data['parse_mode'] = 'HTML';
        if ($keyboard) $data['reply_markup'] = $this->keyboard;

        $this->telegram->sendMessage($data);
    }
    protected function TeruskanPesan($kirim_chat_id)
    {
        $data = [
            'chat_id' => $kirim_chat_id,
            'from_chat_id' => $this->chat_id,
	        'message_id' => $this->message_id
        ];
        $this->telegram->forwardMessage($data);
    }
    protected function KirimPhoto($url,$pic = false,$keyboard = false)
    {
        if ($pic)
        {
            //kalo ada isi link
            $photo = $url;
        }
        else
        {
            $photo = asset('img/tentangbot.jpg');
        }
        $filename = 'tentang.jpg';
        $data = [
            'chat_id' => $this->chat_id,
            'photo' => InputFile::create($photo, $filename),
            'caption' => 'Tentang TeleData BPS Prov. NTB'
        ];
        if ($keyboard) $data['reply_markup'] = $this->keyboard;
        $this->telegram->sendPhoto($data);
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
