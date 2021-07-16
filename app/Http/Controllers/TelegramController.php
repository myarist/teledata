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
        $r = file_get_contents("https://github.com/guangrei/Json-Indonesia-holidays/raw/master/calendar.json");
        $this->hari_libur = json_decode($r, true);
        //keyboard
        $this->keyboard_utama = [
            ['🔰 Konsultasi','🔎 Pencarian','👤 Profil'],
            ['📡 Feedback','🎁 Informasi'],
            ['❌ Selesai']
        ];
        $this->keyboard_utama_admin = [
            ['🔰 Konsultasi','🔎 Pencarian','👤 Profil'],
            ['⚙️ Menu Admin','📡 Feedback','🎁 Informasi'],
            ['❌ Selesai']
        ];
        $this->keyboard_utama_sinkron = [
            ['🔰 Konsultasi','🔎 Pencarian','👤 Profil'],
            ['✴️ Admin Sinkronisasi','📡 Feedback','🎁 Informasi'],
            ['❌ Selesai']
        ];
        $this->keyboard_konsultasi = [
            ['🔙 Kembali']
        ];
        $this->keyboard_menucari = [
            ['📚 Publikasi','📊 Subyek Statistik'],
            ['📋 Berita Resmi Statistik','📌 Lainnya'],
            ['🔙 Kembali']
        ];
        $this->keyboard_menuprofil = [
            ['👤 Edit Nama','📨 Edit Email'],
            ['📞 Edit No HP','📭 Ubah Langganan'],
            ['🔙 Kembali']
        ];
        $this->keyboard_menuprofil_admin = [
            ['👤 Edit Nama','📨 Edit Email'],
            ['📞 Edit No HP','📭 Ubah Langganan'],
            ['🔙 Kembali']
        ];
        $this->keyboard_langganan = [
            ['✅ Menerima Info','📵 Tidak Menerima Info'],
            ['🔙 Menu Profil']
        ];
        $this->keyboard_profil = [
            ['🔙 Menu Profil']
        ];
        $this->keyboard_cari = [
            ['🔙 🔎 Pencarian']
        ];
        $this->keyboard_level1 = [
            ['🔙 Kembali']
        ];
        $this->keyboard_tentangbot = [
            ['📡 Feedback','🔙 Kembali']
        ];
        $this->keyboard_menuadmin = [
            ['💡 Ubah Status Online'],
            ['📂 List Pengunjung','🏷 Log Pencarian'],
            ['📜 List Operator','🧲 List Feedback'],
            ['🔙 Kembali']
        ];
        $this->keyboard_menufeedback = [
            ['⭐️ Beri Feedback','🔙 Kembali']
        ];
        $this->keyboard_tombol_feedback = [
            ['1⭐️','2⭐️','3⭐️','4⭐️','5⭐️'],
            ['🔙 Kembali']
        ];
        $this->keyboard_saran_feedback = [
            ['🔙 Kembali']
        ];
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
            //callback_query
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
            //bila ada ini langsung ke menu awal
            $this->AwalStart();
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
                case '🔎 Pencarian':
                    $this->MenuCari();
                    break;
                case '📚 Publikasi':
                    $this->CariPublikasi();
                    break;
                case '📊 Subyek Statistik':
                    $this->CariStatistik();
                    break;
                case '📋 Berita Resmi Statistik':
                    $this->CariBrs();
                    break;
                case '📌 Lainnya':
                    $this->CariLainnya();
                    break;
                case '🔙 🔎 Pencarian':
                    $this->MenuCari();
                    break;
                case '🔙 Kembali':
                    $this->AwalStart();
                    break;
                case '🎁 Informasi':
                    $this->TentangBot();
                    break;
                case '👤 Profil':
                    $this->MenuProfil();
                    break;
                case '📭 Ubah Langganan':
                    $this->MenuUbahLangganan();
                    break;
                case '✅ Menerima Info':
                    $this->MenerimaInfo();
                    break;
                case '📵 Tidak Menerima Info':
                    $this->TidakMenerimaInfo();
                    break;
                case '🔙 Menu Profil':
                    $this->MenuProfil();
                    break;
                case '👤 Edit Nama':
                    $this->EditNama();
                    break;
                case '📨 Edit Email':
                    $this->EditEmail();
                    break;
                case '🔰 Konsultasi':
                    $this->MenuKonsultasi();
                    break;
                case '📞 Edit No HP':
                    $this->EditNoHp();
                    break;
                case '✴️ Admin Sinkronisasi':
                    $this->AdminSinkronisasi();
                    break;
                case '⚙️ Menu Admin':
                    $this->MenuAdmin();
                    break;
                case '💡 Ubah Status Online':
                    $this->UbahStatusOnline();
                    break;
                case '📡 Feedback':
                    $this->MenuFeedback();
                    break;
                case '⭐️ Beri Feedback':
                    $this->TombolBeriFeedback();
                    break;
                case '📜 List Operator':
                    $this->ListOperator();
                    break;
                case '📂 List Pengunjung':
                    $this->ListPengunjung();
                    break;
                case '🧲 List Feedback':
                    $this->ListFeedback();
                    break;
                case '🏷 Log Pencarian':
                    $this->ListLogPencarian();
                    break;
                case '❌ Selesai':
                    $this->Selesai();
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
                LogPengunjung::create([
                    'username' => $this->username,
                    'chatid' => $this->chat_id,
                    'command' => __FUNCTION__,
                    'msg_id' => $this->message_id
                ]);
                $message = '✳️ Selamat datang <b>'.$data->nama.'</b>'.chr(10);
                $message .= '✳️ <b>TeleDATA (Telegram Data BPSNTB)</b>.'.chr(10);
                $message .= '✳️ <i><b>BPS Provinsi Nusa Tenggara Barat</b></i>'.chr(10);
                $message .= '✳️ Bot Telegram ini akan membantu anda untuk <i>konsultasi langsung</i> atau melakukan <i>pencarian data publikasi, statistik</i> dan <i>berita resmi statistik</i>'.chr(10);
                $message .= '-----------------------------------------------------------' .chr(10);
                $message .='<i>Silakan menggunakan menu dibawah ini</i>.'.chr(10);
                $cek_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->count();
                if ($cek_admin > 0)
                {
                    //admin dan tampilkan keyboard
                    $data_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->first();
                    if ($data_admin->chatid_tg == '')
                    {
                        //admin belum sinkronisasi
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_utama_sinkron,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                    }
                    else
                    {
                        //langsung tampilkan menuadmin
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_utama_admin,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                    }
                }
                else
                {
                    //keyboard biasa
                    $reply_markup = Keyboard::make([
                        'keyboard' => $this->keyboard_utama,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]);
                }

                $response = Telegram::sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => $message,
                    'parse_mode'=> 'HTML',
                    'reply_markup' => $reply_markup
                ]);
                $messageId = $response->getMessageId();
            }

        }
        else
        {
            $message = '✳️ Selamat datang di' .chr(10);
            $message .= '✳️ <b>TeleDATA (Telegram Data BPSNTB)</b>' .chr(10);
            $message .= '✳️ <b>BPS Provinsi Nusa Tenggara Barat</b>' .chr(10);
            $message .= '------------------------------------------------------' .chr(10);
            $message .= '✳️ <i>Untuk dapat menggunakan layanan <b>TeleData</b></i>' .chr(10);
            $message .= '✳️ <i>Anda perlu memasukkan <b>Nama Lengkap</b>, <b>Email</b> dan <b>No HP</b></i>'.chr(10);
            $this->nama = $this->username;
            $data = new DataPengunjung();
            $data->username = $this->username;
            $data->chatid = $this->chat_id;
            $data->save();
            $reply_markup = Keyboard::make([
                'remove_keyboard' => true,
            ]);
            $response = Telegram::sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'parse_mode'=> 'HTML',
                'reply_markup' => $reply_markup
            ]);
            $messageId = $response->getMessageId();
            $this->InputNama();
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
        $message = '🎯 Silakan pilih menu <b>Pencarian Data</b> dibawah ini ' .chr(10);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_menucari,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();

    }
    public function CariPublikasi($hapus = false)
    {
        $message = "📚 Masukkan <b>Kata Kunci </b>untuk <b>Pencarian Publikasi</b> : ";
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_cari,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }

    public function CariBrs()
    {
        $message = "📋 Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Berita Resmi Statistik</b> : ";
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_cari,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();

    }
    public function CariStatistik()
    {
        $message = "📊 Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Statistik</b> : ";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_cari,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }

    public function CariLainnya()
    {
        $message = "📌 Masukkan <b>Kata Kunci</b> untuk <b>Pencarian Lainnya</b> : ";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_cari,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function MenuProfil()
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
                if ($data->flag_berita == 1)
                {
                    $langganan = 'Menerima';
                }
                else
                {
                    $langganan = 'Tidak Menerima';
                }
                $message = 'Anda terdaftar sebagai : ' .chr(10);
                $message .= '✳️ ID : <b>'.$data->chatid.'</b>' .chr(10);
                $message .= '✳️ Username : <b>'.$data->username.'</b>' .chr(10);
                $message .= '-----------------------------------------------------' .chr(10);
                $message .= '♻️ Nama : <b>'.$data->nama.'</b>' .chr(10);
                $message .= '✉️ Email : <b>'.$data->email.'</b>' .chr(10);
                $message .= '📱 No HP : <b>'.$data->nohp.'</b>' .chr(10);
                $message .= '♻️ Langganan Informasi Terkini : <b>'.$langganan.'</b>' .chr(10);
                $message .= '-----------------------------------------------------' .chr(10);
                $message .= '⏰ Register : <b>'.Carbon::parse($data->created_at)->isoFormat('D MMMM Y H:mm:ss').'</b>' .chr(10);
                $message .= '⏱ Update : <b>'.Carbon::parse($data->updated_at)->isoFormat('D MMMM Y H:mm:ss').'</b>' .chr(10);
                $message .= '-----------------------------------------------------' .chr(10);
                $cek_admin = User::where('chatid_tg','=',$this->chat_id)->orWhere('user_tg','=',$this->username)->count();
                if ($cek_admin > 0)
                {
                    //admin
                    $message .= '♻️ Role : Admin Sistem <b>TeleData</b> ('.$this->username.')' .chr(10);
                }

                $reply_markup = Keyboard::make([
                    'keyboard' => $this->keyboard_menuprofil,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                $response = Telegram::sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => $message,
                    'parse_mode'=> 'HTML',
                    'reply_markup' => $reply_markup
                ]);
                $messageId = $response->getMessageId();
            }
        }
        else
        {
            $this->AwalStart();
        }
    }
    public function InputNama()
    {
        $message = "<i>Silakan Masukkan Nama Lengkap anda</i> :";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'remove_keyboard' => true,
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
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
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_profil,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function InputEmail()
    {
        $message = "<i>Silakan masukkan alamat email anda : </i>";
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'remove_keyboard' => true,
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function EditEmail()
    {
        $message = "<i>Silakan masukkan alamat <b>email baru</b> anda : </i>";

        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_profil,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function InputHP()
    {
        $message = "<i>Silakan Masukkan Nomor Handphone anda</i> : ";
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $reply_markup = Keyboard::make([
            'remove_keyboard' => true,
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function EditNoHp()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message = "<i>Silakan masukkan <b>Nomor HP baru</b> anda</i> : ";
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_profil,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
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
        $message = '✅ <b>TENTANG BOT TeleDATA (Telegram Data BPSNTB)</b>' .chr(10);
        $message .= '✅ <b>BPS Provinsi Nusa Tenggara Barat</b>' .chr(10);
        $message .= '✅ Bot Telegram versi 2.' .chr(10);
        $message .= '✅ Dibuat oleh blimika' .chr(10);
        $message .= '-------------------------------------------' .chr(10);
        $message .= '✅ <i>Mohon untuk memberikan saran/kritik melalui menu Feedback dibawah ini</i>' .chr(10);

        $photo = asset('img/tentangbot.jpg');
        $filename = 'tentang.jpg';
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_tentangbot,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $respon = Telegram::sendPhoto([
            'chat_id' => $this->chat_id,
            'photo' => InputFile::create($photo, $filename),
            'caption' => 'Tentang TeleData BPS Prov. NTB',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $respon->getMessageId();
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function MenuUbahLangganan()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message = "<i>Silakan pilih menu dibawah</i> : ";
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_langganan,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function MenerimaInfo()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $count = DataPengunjung::where('chatid','=',$this->chat_id)->count();
        if ($count > 0)
        {
            $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
            $data->flag_berita = 1;
            $data->update();
            $message = '🖥 Status Langganan Informasi Terkini diubah ke <b>MENERIMA</b>'.chr(10);
            $message .= '-------------------------------------------------------'.chr(10);
            $message .= '🟢 Anda akan menerima Informasi Terkini dari BPS Provinsi NTB'.chr(10);
            $message .= '🟢 harap tetap membuka chat bot ini'.chr(10);
        }
        else
        {
            $message = '🔴 ERROR. silakan ulangi lagi'.chr(10);
        }
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML'
        ]);
        $messageId = $response->getMessageId();
        $this->MenuProfil();
    }
    public function TidakMenerimaInfo()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $count = DataPengunjung::where('chatid','=',$this->chat_id)->count();
        if ($count > 0)
        {
            $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
            $data->flag_berita = 0;
            $data->update();
            $message = '🖥 Status Langganan Informasi Terkini diubah ke <b>TIDAK MENERIMA</b>'.chr(10);
            $message .= '-------------------------------------------------------'.chr(10);
            $message .= '🔴 Anda <b>tidak</b> akan menerima Informasi Terkini dari BPS Provinsi NTB'.chr(10);
        }
        else
        {
            $message = '🔴 ERROR. silakan ulangi lagi'.chr(10);
        }
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML'
        ]);
        $messageId = $response->getMessageId();
        $this->MenuProfil();
    }
    public function MenuKonsultasi()
    {
        $message = '🔰 <b>LAYANAN KONSULTASI ONLINE</b> 🔰' .chr(10);
        $message .= '------------------------------------------' .chr(10);
        $message .= '🟢 Hari Layanan :  <b>Senin - Jumat (Kecuali hari libur)</b>' .chr(10);
        $message .= '🟢 Jam Layanan : <b>08.00 - 15.00 WITA</b>' .chr(10);
        $message .= '------------------------------------------' .chr(10);

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
                $message .= '🚫 <b>Hari Libur : '.$this->hari_libur[Carbon::now()->format("Ymd")]['deskripsi'].'</b>' .chr(10);
                $message .= '------------------------------------------' .chr(10);
                $message .= '🔅 <b>Silakan tinggalkan pesan</b>'.chr(10);
                $message .= '🔅 Pesan anda akan terbaca saat operator Online' .chr(10);
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
                            $message .= '🟢🟢 <b>OPERATOR ONLINE</b> 🟢🟢' .chr(10);
                        }
                        else
                        {
                            $message .= '🔴🔴 <b>BELUM ADA OPERATOR ONLINE</b> 🔴🔴' .chr(10);
                            $message .= '------------------------------------------' .chr(10);
                            $message .= '🔅 Pesan anda akan terbaca saat operator Online ' .chr(10);
                        }
                    }
                    else
                    {
                        //sudah jam 3 sore dan tutup
                        $message .= '🚫 <b>DILUAR JAM LAYANAN</b> 🚫'.chr(10);
                        $message .= '------------------------------------------' .chr(10);
                        $message .= '🔅 <b>Silakan tinggalkan pesan</b>' .chr(10);
                        $message .= '🔅 Pesan anda akan terbaca saat operator Online '.chr(10);
                    }

                }
                else
                {
                    //diluar jam layanan
                    $message .= '🚫 <b>DILUAR JAM LAYANAN</b> 🚫' .chr(10);
                    $message .= '------------------------------------------' .chr(10);
                    $message .= '🔅 <b>Silakan tinggalkan pesan</b>' .chr(10);
                    $message .= '🔅 Pesan anda akan terbaca saat operator Online '.chr(10);
                }
            }
        }
        else
        {
            //hari sabtu dan minggu
            $message .= '🚫 <b>DILUAR HARI dan JAM LAYANAN</b> 🚫' .chr(10);
            $message .= '------------------------------------------' .chr(10);
            $message .= '🔅 <b>Silakan tinggalkan pesan</b>' .chr(10);
            $message .= '🔅 Pesan anda akan terbaca saat operator Online'.chr(10);

        }
        $message .= '------------------------------------------' .chr(10);
        $message .= '❓ <i>Masukkan pertanyaan untuk operator</i> :' .chr(10);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_konsultasi,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $messageId
        ]);
    }
    public function AdminSinkronisasi()
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

            $message ='✅ Data admin <b>'.$this->username.'</b> sudah disinkron'.chr(10);
        }
        else
        {
            //bukan admin
            $message ='❌ Anda bukan admin sistem'.chr(10);
        }
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML'
        ]);
        $messageId = $response->getMessageId();
        $this->AwalStart();
        //$this->MenuAdmin();
    }
    public function MenuAdmin()
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
                $flag_statusonline = '🟢 ONLINE';
            }
            else
            {
                $flag_statusonline = '🔴 OFFLINE';
            }
            if ($data_admin->aktif == 1)
            {
                $flag_aktif = '🟢 AKTIF';
            }
            else
            {
                $flag_aktif = '🔴 NONAKTIF';
            }
            if ($data_admin->lastip != '')
            {
                $lastlogin = $data_admin->lastip .' ('. Carbon::parse($data_admin->lastlogin)->format('d M Y H:i') .')';
            }
            else
            {
                $lastlogin ='';
            }
            $message = '⚙️<b>Menu Admin TeleData</b>' .chr(10);
            $message .= '--------------------------------------------' .chr(10);
            $message .= '🌀 Nama : <b>'.$data_admin->nama.'</b>' .chr(10);
            $message .= '🌀 Username (Web) : <b>'.$data_admin->username.'</b>' .chr(10);
            $message .= '🌀 Username (TG) : <b>'.$data_admin->user_tg.'</b>' .chr(10);
            $message .= '🌀 Chat ID (TG) : <b>'.$data_admin->chatid_tg.'</b>' .chr(10);
            $message .= '--------------------------------------------' .chr(10);
            $message .= '🌀 Email : <b>'.$data_admin->email.'</b>' .chr(10);
            $message .= '🌀 Lastlogin : <b>'.$lastlogin.'</b>' .chr(10);
            $message .= '--------------------------------------------' .chr(10);
            $message .= '🌀 Status Akun : <b>'.$flag_aktif.'</b>' .chr(10);
            $message .= '🌀 Status Online : <b>'.$flag_statusonline.'</b>' .chr(10);

            $reply_markup = Keyboard::make([
                'keyboard' => $this->keyboard_menuadmin,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            $response = Telegram::sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'parse_mode'=> 'HTML',
                'reply_markup' => $reply_markup
            ]);
            $messageId = $response->getMessageId();
        }
        else
        {
            //bukan admin
            $message ='❌ Anda bukan admin sistem'.chr(10);
            $response = Telegram::sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'parse_mode'=> 'HTML'
            ]);
            $messageId = $response->getMessageId();
            $this->AwalStart();
        }

    }
    public function UbahStatusOnline()
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
                $flag_statusonline = '🔴 OFFLINE';
            }
            else
            {
                $flag_konsultasi = 1;
                $flag_statusonline = '🟢 ONLINE';
            }
            $data_admin->status_online = $flag_konsultasi;
            $data_admin->update();
            $message = '🌀 Status Online berhasil diubah ke '.$flag_statusonline .chr(10);

            $response = Telegram::sendMessage([
                'chat_id' => $this->chat_id,
                'text' => $message,
                'parse_mode'=> 'HTML',
            ]);
            $messageId = $response->getMessageId();
            $this->MenuAdmin();
        }
        else
        {
          //bukan admin
          $message ='❌ Anda bukan admin sistem'.chr(10);
          $response = Telegram::sendMessage([
              'chat_id' => $this->chat_id,
              'text' => $message,
              'parse_mode'=> 'HTML'
          ]);
          $messageId = $response->getMessageId();
          $this->AwalStart();
        }
    }
    public function MenuFeedback()
    {
        //cek dulu feedbacknya
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);

        $cek_fb = LogFeedback::where('chatid',$this->chat_id)->count();
        if ($cek_fb > 0)
        {
            $message = '🏆 Feedback Bapak/Ibu terhadap <b>TeleDATA</b>'.chr(10);
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
            $message .= '-----------------------------------'.chr(10);
            $message .= '🟢 <b>Nilai</b> : '. $nilai .chr(10);
            $message .= '-----------------------------------'.chr(10);
            $message .= '🔋 <b>Komentar</b> : <i>'.$data->isi_feedback.'</i>' .chr(10);
            $message .= '-----------------------------------'.chr(10);
            $message .= '🕰 <b>Tanggal</b> : '.\Carbon\Carbon::parse($data->updated_at)->format('j F Y H:i:s') .chr(10);
        }
        else
        {
            $message = '🏆 <b>Belum ada feedback dari Bapak/Ibu</b>' .chr(10);
            $message .= '<i>Silakan untuk memberikan penilaian dan saran/kritik untuk kemajuan TeleData</i>' .chr(10);
        }
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_menufeedback,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function TombolBeriFeedback()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => 'FeedbackBintang',
            'msg_id' => $this->message_id
        ]);
        $message = '🏆 <b>Silakan pilih nilai dibawah ini</b>' .chr(10);
        $reply_markup = Keyboard::make([
            'keyboard' => $this->keyboard_tombol_feedback,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }
    public function ListOperator()
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
            //cek jumalh record
            //apabila lebih dari 30 record bagi per 30 record dikirimkan
            //cek jumlah total dulu
            $jumlah_total = User::count();
            $item_per_hal = 16;
            $i=1;
            if ($jumlah_total > $item_per_hal)
            {
                $hal = ceil($jumlah_total/$item_per_hal);
                if ($hal > 5)
                {
                    $hal = 5;
                }
                    for ($j = 1 ; $j <= $hal; $j++)
                    {
                        $data = User::orderBY('created_at','desc')->skip((($j-1)*$item_per_hal))->take($item_per_hal)->get();
                        $message = '📀 Data '.$jumlah_total.' Operator TeleData terakhir 📀' .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
                        $message .= 'Halaman '.$j .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
                        foreach ($data as $item) {
                            if ($item->lastip != '')
                            {
                                $lastlogin = $item->lastip .' ('. Carbon::parse($item->lastlogin)->format('d M Y H:i:s') .')';
                            }
                            else
                            {
                                $lastlogin ='Belum pernah login';
                            }
                            if ($item->status_online == 1)
                            {
                                $stat_online = '🟢 ONLINE';
                            }
                            else
                            {
                                $stat_online = '🔴 OFFLINE';
                            }
                            $message .= '🟢 Nama: <b>'.$item->nama .'</b>'.chr(10);
                            $message .= '🟢 Email: <b>'. $item->email .'</b>'.chr(10);
                            $message .= '🟢 user_tg: <b>'.$item->user_tg.'</b>'.chr(10);
                            $message .= '🟢 chat_id: <b>'.$item->chatid_tg.'</b>'.chr(10);
                            $message .= '🟢 username: <b>'.$item->username.'</b>'.chr(10);
                            $message .= '🟢 status_online: <b>'.$stat_online.'</b>'.chr(10);
                            $message .= '🟢 lastlogin: <b>'. $lastlogin .'</b>'.chr(10);
                            $message .= '---------------------------------------------'.chr(10);
                        }
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_menuadmin,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();

                    }
            }
            else
            {
                $data = User::orderBY('created_at','desc')->get();
                $message = '📀 Data '.$jumlah_total.' Operator TeleData terakhir 📀' .chr(10);
                $message .= '-------------------------------------------' .chr(10);
                foreach ($data as $item) {
                    if ($item->lastip != '')
                    {
                        $lastlogin = $item->lastip .' ('. Carbon::parse($item->lastlogin)->format('d M Y H:i:s') .')';
                    }
                    else
                    {
                        $lastlogin ='Belum pernah login';
                    }
                    if ($item->status_online == 1)
                    {
                        $stat_online = '🟢 ONLINE';
                    }
                    else
                    {
                        $stat_online = '🔴 OFFLINE';
                    }
                    $message .= '🟢 Nama: <b>'.$item->nama .'</b>'.chr(10);
                    $message .= '🟢 Email: <b>'. $item->email .'</b>'.chr(10);
                    $message .= '🟢 user_tg: <b>'.$item->user_tg.'</b>'.chr(10);
                    $message .= '🟢 chat_id: <b>'.$item->chatid_tg.'</b>'.chr(10);
                    $message .= '🟢 username: <b>'.$item->username.'</b>'.chr(10);
                    $message .= '🟢 status_online: <b>'.$stat_online.'</b>'.chr(10);
                    $message .= '🟢 lastlogin: <b>'. $lastlogin .'</b>'.chr(10);
                    $message .= '---------------------------------------------'.chr(10);
                }
                $reply_markup = Keyboard::make([
                    'keyboard' => $this->keyboard_menuadmin,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                $response = Telegram::sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => $message,
                    'parse_mode'=> 'HTML',
                    'reply_markup' => $reply_markup
                ]);
                $messageId = $response->getMessageId();
            }
        }
        else
        {
           //bukan admin
           $message ='❌ Anda bukan admin sistem'.chr(10);
           $response = Telegram::sendMessage([
               'chat_id' => $this->chat_id,
               'text' => $message,
               'parse_mode'=> 'HTML'
           ]);
           $messageId = $response->getMessageId();
           $this->AwalStart();

        }
    }
    public function ListPengunjung()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $cek_dulu = User::where('chatid_tg','=',$this->chat_id)->count();
        //cek dulu apakah admin aapa tidak
        if ($cek_dulu > 0)
        {
            //cek jumalh record
            //apabila lebih dari 30 record bagi per 30 record dikirimkan
            //cek jumlah total dulu
                $jumlah_total = DataPengunjung::count();
                $item_per_hal = 16;
                $i=1;
                //cek dulu apakah lebih dari 15
                //bila lebih 15 langsung kirim
                if ($jumlah_total > $item_per_hal)
                {
                    //$hal = 31 % 15 =
                    $hal = ceil($jumlah_total/$item_per_hal);
                    if ($hal > 5)
                    {
                        $hal = 5;
                    }
                    for ($j = 1 ; $j <= $hal; $j++)
                    {
                        $data = DataPengunjung::orderBY('created_at','desc')->skip((($j-1)*$item_per_hal))->take($item_per_hal)->get();
                        $message = '📀 Data '.$jumlah_total.' Pengunjung TeleData 📀' .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
                        $message .= 'Halaman : '.$j .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
                        foreach ($data as $item) {
                            $message .= '👤 Nama : <b>'.$item->nama .'</b>' .chr(10);
                            $message .= '📩 Email : <b>'. $item->email .'</b>' .chr(10);
                            $message .= '📱 No Handphone : <b>'.$item->nohp.'</b>' .chr(10);
                            $message .= '🖥 Chat ID : <b>'.$item->chatid.'</b>' .chr(10);
                            $message .= '🕹 Username: <b>'.$item->username.'</b>' .chr(10);
                            $message .= '⏱ Register: <b>'. Carbon::parse($item->created_at)->format('d M Y H:i') .'</b>'.chr(10);
                            $message .= '⏱ Update: <b>'. Carbon::parse($item->updated_at)->format('d M Y H:i') .'</b>'.chr(10);
                            $message .= '-----------------------------------------------' .chr(10);
                        }
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_menuadmin,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                    }
                }
                else
                {
                    $data = DataPengunjung::orderBY('created_at','desc')->get();
                    $message = '📀 Data '.$jumlah_total.' Pengunjung TeleData 📀' .chr(10);
                    $message .= '-------------------------------------------' .chr(10);
                    foreach ($data as $item) {
                        $message .= '👤 Nama : <b>'.$item->nama .'</b>' .chr(10);
                        $message .= '📩 Email : <b>'. $item->email .'</b>' .chr(10);
                        $message .= '📱 No Handphone : <b>'.$item->nohp.'</b>' .chr(10);
                        $message .= '🖥 Chat ID : <b>'.$item->chatid.'</b>' .chr(10);
                        $message .= '🕹 Username: <b>'.$item->username.'</b>' .chr(10);
                        $message .= '⏱ Daftar: <b>'. Carbon::parse($item->created_at)->format('d M Y H:i') .'</b>'.chr(10);
                        $message .= '-----------------------------------------------' .chr(10);

                    }
                    $reply_markup = Keyboard::make([
                        'keyboard' => $this->keyboard_menuadmin,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]);
                    $response = Telegram::sendMessage([
                        'chat_id' => $this->chat_id,
                        'text' => $message,
                        'parse_mode'=> 'HTML',
                        'reply_markup' => $reply_markup
                    ]);
                    $messageId = $response->getMessageId();
                }
        }
        else
        {
           //bukan admin
           $message ='❌ Anda bukan admin sistem'.chr(10);
           $response = Telegram::sendMessage([
               'chat_id' => $this->chat_id,
               'text' => $message,
               'parse_mode'=> 'HTML'
           ]);
           $messageId = $response->getMessageId();
           $this->AwalStart();

        }
    }
    public function ListFeedback()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        //cek dulu admin ato ngga
        $cek_dulu = User::where('chatid_tg','=',$this->chat_id)->count();
        //cek dulu apakah admin aapa tidak
        if ($cek_dulu > 0)
        {
            //cek jumalh record
            //apabila lebih dari 30 record bagi per 30 record dikirimkan
            //cek jumlah total dulu
                $jumlah_total = LogFeedback::orderBY('created_at','desc')->count();;
                $item_per_hal = 15;
                $i=1;
                //cek dulu apakah lebih dari 15
                //bila lebih 15 langsung kirim
                if ($jumlah_total > $item_per_hal)
                {
                    //$hal = 31 % 15 =
                    $hal = ceil($jumlah_total/$item_per_hal);
                    if ($hal > 5)
                    {
                        $hal = 5;
                    }
                    for ($j = 1 ; $j <= $hal; $j++)
                    {
                        $data = LogFeedback::orderBY('created_at','desc')->skip((($j-1)*$item_per_hal))->take($item_per_hal)->get();
                        $message = '📀 Data '.$jumlah_total.' Feedback Pengunjung <b>TeleDATA</b> terakhir' .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
                        $message .= 'Halaman : '.$j .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
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

                            $message .= '👤 Nama : <b>'.$item->Pengunjung->nama .'</b>' .chr(10);
                            $message .= '📩 Email : <b>'. $item->Pengunjung->email .'</b>' .chr(10);
                            $message .= '📱 No Handphone : <b>'.$item->Pengunjung->nohp.'</b>' .chr(10);
                            $message .= '🖥 Chat ID : <b>'.$item->chatid.'</b>' .chr(10);
                            $message .= '🕹 Username: <b>'.$item->username.'</b>' .chr(10);
                            $message .= '⏱ Tanggal : <b>'. Carbon::parse($item->created_at)->format('d M Y H:i:s') .'</b>'.chr(10);
                            $message .= '🏆 Nilai : '.$nilai .chr(10);
                            $message .= '📝 Komentar : <i>'.$item->isi_feedback.'</i>' .chr(10);
                            $message .= '-----------------------------------------------' .chr(10);
                        }
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_menuadmin,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                    }
                }
                else
                {
                    $data = LogFeedback::orderBY('created_at','desc')->get();
                    $message = '📀 Data '.$jumlah_total.' Feedback Pengunjung <b>TeleDATA</b> terakhir' .chr(10);
                    $message .= '-------------------------------------------' .chr(10);
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

                        $message .= '👤 Nama : <b>'.$item->Pengunjung->nama .'</b>' .chr(10);
                        $message .= '📩 Email : <b>'. $item->Pengunjung->email .'</b>' .chr(10);
                        $message .= '📱 No Handphone : <b>'.$item->Pengunjung->nohp.'</b>' .chr(10);
                        $message .= '🖥 Chat ID : <b>'.$item->chatid.'</b>' .chr(10);
                        $message .= '🕹 Username: <b>'.$item->username.'</b>' .chr(10);
                        $message .= '⏱ Tanggal : <b>'. Carbon::parse($item->created_at)->format('d M Y H:i:s') .'</b>'.chr(10);
                        $message .= '🏆 Nilai : '.$nilai .chr(10);
                        $message .= '📝 Komentar : <i>'.$item->isi_feedback.'</i>' .chr(10);
                        $message .= '-----------------------------------------------' .chr(10);

                    }
                    $reply_markup = Keyboard::make([
                        'keyboard' => $this->keyboard_menuadmin,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]);
                    $response = Telegram::sendMessage([
                        'chat_id' => $this->chat_id,
                        'text' => $message,
                        'parse_mode'=> 'HTML',
                        'reply_markup' => $reply_markup
                    ]);
                    $messageId = $response->getMessageId();
                }
        }
        else
        {
           //bukan admin
           $message ='❌ Anda bukan admin sistem'.chr(10);
           $response = Telegram::sendMessage([
               'chat_id' => $this->chat_id,
               'text' => $message,
               'parse_mode'=> 'HTML'
           ]);
           $messageId = $response->getMessageId();
           $this->AwalStart();

        }
    }
    public function ListLogPencarian()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $cek_dulu = User::where('chatid_tg','=',$this->chat_id)->count();
        //cek dulu apakah admin aapa tidak
        if ($cek_dulu > 0)
        {
            //cek jumalh record
            //apabila lebih dari 30 record bagi per 30 record dikirimkan
            //cek jumlah total dulu
                $jumlah_total = LogCari::orderBy('created_at','desc')->count();
                $item_per_hal = 15;
                $i=1;
                //cek dulu apakah lebih dari 15
                //bila lebih 15 langsung kirim
                if ($jumlah_total > $item_per_hal)
                {
                    //$hal = 31 % 15 =
                    $hal = ceil($jumlah_total/$item_per_hal);
                    if ($hal > 5)
                    {
                        $hal = 5;
                    }
                    for ($j = 1 ; $j <= $hal; $j++)
                    {
                        $data = LogCari::orderBy('created_at','desc')->skip((($j-1)*$item_per_hal))->take($item_per_hal)->get();
                        $message = '📀 Data '.$jumlah_total.' Keyword Pencarian terakhir di TeleData' .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
                        $message .= 'Halaman : '.$j .chr(10);
                        $message .= '-------------------------------------------' .chr(10);
                        foreach ($data as $item) {
                            $message .= '👤 Nama : <b>'.$item->Pengunjung->nama .'</b>' .chr(10);
                            $message .= '📩 Email : <b>'. $item->Pengunjung->email .'</b>' .chr(10);
                            $message .= '📱 No Handphone : <b>'.$item->Pengunjung->nohp.'</b>' .chr(10);
                            $message .= '🖥 Chat ID : <b>'.$item->chatid.'</b>' .chr(10);
                            $message .= '🕹 Username: <b>'.$item->username.'</b>' .chr(10);
                            $message .= '🔎 Keyword: ('.$item->command.') <b>'. $item->keyword .'</b>' .chr(10);
                            $message .= '⏱ Tanggal : <b>'. Carbon::parse($item->created_at)->format('d M Y H:i') .'</b>'.chr(10);
                            $message .= '-----------------------------------------------' .chr(10);
                        }
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_menuadmin,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                    }
                }
                else
                {
                    $data = LogCari::orderBy('created_at','desc')->get();
                    $message = '📀 Data '.$jumlah_total.' Keyword Pencarian terakhir di TeleData' .chr(10);
                    $message .= '-------------------------------------------' .chr(10);
                    foreach ($data as $item) {
                        $message .= '👤 Nama : <b>'.$item->Pengunjung->nama .'</b>' .chr(10);
                        $message .= '📩 Email : <b>'. $item->Pengunjung->email .'</b>' .chr(10);
                        $message .= '📱 No Handphone : <b>'.$item->Pengunjung->nohp.'</b>' .chr(10);
                        $message .= '🖥 Chat ID : <b>'.$item->chatid.'</b>' .chr(10);
                        $message .= '🕹 Username: <b>'.$item->username.'</b>' .chr(10);
                        $message .= '🔎 Keyword: ('.$item->command.') <b>'. $item->keyword .'</b>' .chr(10);
                        $message .= '⏱ Tanggal : <b>'. Carbon::parse($item->created_at)->format('d M Y H:i') .'</b>'.chr(10);
                        $message .= '-----------------------------------------------' .chr(10);
                    }
                    $reply_markup = Keyboard::make([
                        'keyboard' => $this->keyboard_menuadmin,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]);
                    $response = Telegram::sendMessage([
                        'chat_id' => $this->chat_id,
                        'text' => $message,
                        'parse_mode'=> 'HTML',
                        'reply_markup' => $reply_markup
                    ]);
                    $messageId = $response->getMessageId();
                }
        }
        else
        {
           //bukan admin
           $message ='❌ Anda bukan admin sistem'.chr(10);
           $response = Telegram::sendMessage([
               'chat_id' => $this->chat_id,
               'text' => $message,
               'parse_mode'=> 'HTML'
           ]);
           $messageId = $response->getMessageId();
           $this->AwalStart();

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
                        'required' => '⚠️ :attribute wajib terisi!!!',
                        'string'=> '⚠️ :attribute harus berupa karakter',
                        'regex'=> '⚠️ :attribute harus berupa karakter',
                        'min' => '⚠️ :attribute harus diisi minimal :min karakter!!!',
                        'max' => '⚠️ :attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Nama' => $this->text],
                            ['Nama' => 'string|min:3|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $reply_markup = Keyboard::make([
                            'remove_keyboard' => true,
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        $this->InputNama();
                    }
                    else
                    {
                        $message ='';
                        $message .='✅ Nama <b>'.$this->text.'</b> berhasil disimpan'.chr(10);
                        $message .='---------------------------------------------'.chr(10);
                        $message .='<i>Silakan masukkan email anda</i> :' . chr(10);
                        $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                        $data->nama = $this->text;
                        $data->update();

                        $tg->command = 'InputEmail';
                        $tg->update();

                        $reply_markup = Keyboard::make([
                            'remove_keyboard' => true,
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                    }

                }
                elseif ($tg->command == 'EditNama') {
                    $pesan_error = [
                        'required' => '⚠️ :attribute wajib terisi!!!',
                        'string'=> '⚠️ :attribute harus berupa karakter',
                        'regex'=> '⚠️ :attribute harus berupa karakter',
                        'min' => '⚠️ :attribute harus diisi minimal :min karakter!!!',
                        'max' => '⚠️ :attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Nama' => $this->text],
                            ['Nama' => 'string|min:3|max:50|regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_profil,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        $this->EditNama();
                    }
                    else
                    {
                        $message ='';
                        $message .='🆗 Nama <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                        $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                        $data->nama = $this->text;
                        $data->update();

                        $tg->command = 'showMenu';
                        $tg->update();

                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_profil,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $this->MenuProfil();
                    }
                }
                elseif ($tg->command == 'InputEmail')
                {
                    $pesan_error = [
                        'required' => '⚠️ :attribute wajib terisi!!!',
                        'email'=> '⚠️ :attribute harus alamat lengkap',
                        'regex'=> '⚠️ :attribute harus berupa alamat yang valid',
                        'min' => '⚠️ :attribute harus diisi minimal :min karakter!!!',
                        'max' => '⚠️ :attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Email' => $this->text],
                            ['Email' => 'required|email|regex:/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $reply_markup = Keyboard::make([
                            'remove_keyboard' => true,
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        $this->InputEmail();
                    }
                    else
                    {
                        $message ='';
                        $message .='✅ Email <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                        $message .='<i>Silakan Masukkan nomor HP anda</i> :' . chr(10);
                        $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                        $data->email = $this->text;
                        $data->update();

                        $tg->command = 'InputHP';
                        $tg->update();

                        $reply_markup = Keyboard::make([
                            'remove_keyboard' => true,
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                    }

                }
                elseif ($tg->command == 'EditEmail')
                {
                    $pesan_error = [
                        'required' => '⚠️ :attribute wajib terisi!!!',
                        'email'=> '⚠️ :attribute harus alamat lengkap',
                        'regex'=> '⚠️ :attribute harus berupa alamat yang valid',
                        'min' => '⚠️ :attribute harus diisi minimal :min karakter!!!',
                        'max' => '⚠️ :attribute harus diisi maksimal :max karakter!!!',
                    ];
                    $validator = Validator::make(['Email' => $this->text],
                            ['Email' => 'required|email|regex:/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_profil,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        $this->EditEmail();
                    }
                    else
                    {
                        $message ='';
                        $message .='✅ Email <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                        $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                        $data->email = $this->text;
                        $data->update();

                        $tg->command = 'showMenu';
                        $tg->update();
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_profil,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $this->MenuProfil();
                    }

                }
                elseif ($tg->command == 'EditNoHp')
                {
                    $pesan_error = [
                        'required' => '⚠️ :attribute wajib terisi!!!',
                        'regex'=> '⚠️ :attribute harus berupa angka',
                        'min' => '⚠️ :attribute harus diisi minimal :min angka!!!',
                        'max' => '⚠️ :attribute harus diisi maksimal :max angka!!!',
                    ];
                    $validator = Validator::make(['Nohp' => $this->text],
                            ['Nohp' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:13'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_profil,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        $this->EditNoHp();
                    }
                    else
                    {
                        $message ='';
                        $message .='✅ Nomor HP <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                        $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                        $data->nohp = $this->text;
                        $data->update();

                        $tg->command = 'showMenu';
                        $tg->update();

                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_profil,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $this->MenuProfil();
                    }
                }
                elseif ($tg->command == 'InputHP')
                {
                    $pesan_error = [
                        'required' => '⚠️ :attribute wajib terisi!!!',
                        'regex'=> '⚠️ :attribute harus berupa angka',
                        'min' => '⚠️ :attribute harus diisi minimal :min angka!!!',
                        'max' => '⚠️ :attribute harus diisi maksimal :max angka!!!',
                    ];
                    $validator = Validator::make(['Nohp' => $this->text],
                            ['Nohp' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:13'],$pesan_error
                        );
                    if ($validator->fails()) {
                        // your code
                        $message = $validator->errors()->first() .chr(10);
                        $reply_markup = Keyboard::make([
                            'remove_keyboard' => true,
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        $this->InputHP();
                    }
                    else
                    {
                        $message ='';
                        $message .='✅ Nomor HP <b>'.$this->text.'</b> berhasil disimpan' . chr(10) .chr(10);
                        $data = DataPengunjung::where('chatid','=',$this->chat_id)->first();
                        $data->nohp = $this->text;
                        $data->update();

                        $tg->command = 'AwalStart';
                        $tg->update();

                        $reply_markup = Keyboard::make([
                            'remove_keyboard' => true,
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        $this->AwalStart();
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

                            if ($total_tabel > 10)
                            {
                                $total_tabel = 10;
                            }
                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $message =''; //kosongin message;
                                $message ='📚 Hasil Pencarian Publikasi 📚' . chr(10);
                                $message .='🔐 Kata kunci : <b>'.$this->text.'</b>' . chr(10);
                                $message .= '📘 Halaman : '. $i .chr(10);
                                $message .='--------------------------------------------'.chr(10);
                                $respon = $h->caripublikasi($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $message .= '🟢 Judul : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= '🟢 Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> 🔹 <a href="'.$item['pdf'].'">Download PDF</a> ('.$item['size'].')'.chr(10);
                                    $message .='--------------------------------------------'.chr(10);
                                }
                                $response = Telegram::sendMessage([
                                    'chat_id' => $this->chat_id,
                                    'text' => $message,
                                    'parse_mode'=> 'HTML',
                                    'disable_web_page_preview'=> true,
                                ]);
                                $messageId = $response->getMessageId();

                            }
                        }
                        else
                        {
                            $message ='';
                            $message ='📚 Hasil Pencarian Publikasi 📚' . chr(10);
                            $message .='🔐 Kata kunci : <b>'.$this->text.'</b>' . chr(10);
                            $message .='--------------------------------------------'.chr(10);

                            foreach ($response['data'][1] as $item)
                            {
                                $message .= '🟢 Judul: <b>'.$item['title'].'</b>' .chr(10);
                                $message .= '🟢 Rilis: <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> 🔹 <a href="'.$item['pdf'].'">Download PDF</a> ('.$item['size'].')'.chr(10);
                                $message .='--------------------------------------------'.chr(10);
                            }
                            $response = Telegram::sendMessage([
                                'chat_id' => $this->chat_id,
                                'text' => $message,
                                'parse_mode'=> 'HTML',
                                'disable_web_page_preview'=> true,
                            ]);
                            $messageId = $response->getMessageId();
                        }

                    }
                    else
                    {
                        $message ='📚 Publikasi yang anda cari tidak tersedia' .chr(10);
                        $message .= '<i>Ulangi pencarian publikasi</i>' .chr(10);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'disable_web_page_preview'=> true,
                        ]);
                        $messageId = $response->getMessageId();
                    }

                    //$tg->command = 'showMenu';
                    //$tg->update();
                    $this->CariPublikasi();

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
                            if ($total_tabel > 10)
                            {
                                $total_tabel = 10;
                            }

                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $message ='';
                                $message ='📊 Hasil Pencarian <b>Tabel Statistik</b> : ' . chr(10);
                                $message .='🔐 Kata kunci : <b>'.$this->text.'</b>' . chr(10);
                                $message .= '📘 Halaman : '. $i .chr(10);
                                $message .='--------------------------------------------'.chr(10);
                                $respon = $h->caristatistik($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $message .= '🔵 Judul Tabel : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= '🔵 Update : <b>'.\Carbon\Carbon::parse($item['updt_date'])->format('d M Y').'</b> 🔸 <a href="'.$item['excel'].'">Download Tabel</a> ('.$item['size'].')' .chr(10);
                                    $message .='--------------------------------------------'.chr(10);
                                }
                                $respon = Telegram::sendMessage([
                                    'chat_id' => $this->chat_id,
                                    'text' => $message,
                                    'parse_mode'=> 'HTML',
                                    'disable_web_page_preview'=> true,
                                ]);
                                $messageId = $respon->getMessageId();
                            }

                        }
                        else
                        {
                            $message ='📊 Hasil Pencarian <b>Tabel Statistik</b> : ' . chr(10);

                            foreach ($response['data'][1] as $item)
                            {

                                $message .= '🔵 Judul Tabel : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= '🔵 Update : <b>'.\Carbon\Carbon::parse($item['updt_date'])->format('d M Y').'</b> 🔸 <a href="'.$item['excel'].'">Download Tabel</a> ('.$item['size'].')' .chr(10);
                                $message .='--------------------------------------------'.chr(10);
                            }
                            $respon = Telegram::sendMessage([
                                'chat_id' => $this->chat_id,
                                'text' => $message,
                                'parse_mode'=> 'HTML',
                                'disable_web_page_preview'=> true,
                            ]);
                            $messageId = $respon->getMessageId();
                        }

                    }
                    else
                    {
                        $message ='📊 <b>Tabel Statistik</b> yang anda cari tidak tersedia' .chr(10);
                        $message .= '<i>Ulangi pencarian tabel statistik</i>' .chr(10);
                        $respon = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'disable_web_page_preview'=> true,
                        ]);
                        $messageId = $respon->getMessageId();
                    }

                    $this->CariStatistik();
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
                            if ($total_tabel > 10)
                            {
                                $total_tabel = 10;
                            }
                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $message =''; //kosongin message;
                                $message ='📋 Hasil Pencarian <b>Berita Resmi Statistik</b> 📋' . chr(10);
                                $message .='🔐 Kata kunci : <b>'.$this->text.'</b>' . chr(10);
                                $message .= '📘 Halaman : '. $i .chr(10);
                                $message .='--------------------------------------------'.chr(10);
                                $respon = $h->caribrs($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $message .= '🟢 Judul : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= '🟢 Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> 🔹 <a href="'.$item['pdf'].'">Download PDF</a> ('.$item['size'].')'.chr(10);
                                    $message .='--------------------------------------------'.chr(10);
                                }
                                $respon = Telegram::sendMessage([
                                    'chat_id' => $this->chat_id,
                                    'text' => $message,
                                    'parse_mode'=> 'HTML',
                                    'disable_web_page_preview'=> true,
                                ]);
                                $messageId = $respon->getMessageId();

                            }
                        }
                        else
                        {
                            $message =''; //kosongin message;
                            $message ='📋 Hasil Pencarian <b>Berita Resmi Statistik</b> 📋' . chr(10);
                            $message .='🔐 Kata kunci : <b>'.$this->text.'</b>' . chr(10);
                            $message .='--------------------------------------------'.chr(10);

                            foreach ($response['data'][1] as $item)
                            {

                                $message .= '🟢 Judul : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= '🟢 Rilis : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> 🔹 <a href="'.$item['pdf'].'">Download PDF</a> ('.$item['size'].')'.chr(10);
                                $message .='--------------------------------------------'.chr(10);
                            }
                            $respon = Telegram::sendMessage([
                                'chat_id' => $this->chat_id,
                                'text' => $message,
                                'parse_mode'=> 'HTML',
                                'disable_web_page_preview'=> true,
                            ]);
                            $messageId = $respon->getMessageId();
                        }

                    }
                    else
                    {
                        $message ='📋 <b>Pencarian Berita Resmi Statistik</b> yang anda cari tidak tersedia' .chr(10);
                        $message .= '<i>Ulangi pencarian lainnya</i>' .chr(10);
                        $respon = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'disable_web_page_preview'=> true,
                        ]);
                        $messageId = $respon->getMessageId();
                    }
                    $this->CariBrs();
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
                            if ($total_tabel > 10)
                            {
                                $total_tabel = 10;
                            }
                            for ($i = 1; $i <= $total_tabel; $i++)
                            {
                                $message ='';
                                $message ='📌 Hasil Pencarian <b>Lainnya</b> : '.chr(10);
                                $message .='🔐 Kata kunci : <b>'.$this->text.'</b>' . chr(10);
                                $message .= '📘 Halaman : '. $i .chr(10);
                                $message .='--------------------------------------------'.chr(10);
                                $respon = $h->carilain($keyword,$i);
                                foreach ($respon['data'][1] as $item)
                                {
                                    $url_link = explode("-",$item['rl_date']);
                                    $link = 'https://ntb.bps.go.id/news/'.$url_link[0].'/'.$url_link[1].'/'.$url_link[2].'/'.$item['news_id'].'/bpsntb.html';
                                    $message .= '🟤 Judul : <b>'.$item['title'].'</b>' .chr(10);
                                    $message .= '🟤 Tanggal : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> 🔺 <a href="'.$link.'">Link</a>' .chr(10);
                                    $message .='--------------------------------------------'.chr(10);
                                }
                                $respon = Telegram::sendMessage([
                                    'chat_id' => $this->chat_id,
                                    'text' => $message,
                                    'parse_mode'=> 'HTML',
                                    'disable_web_page_preview'=> true,
                                ]);
                                $messageId = $respon->getMessageId();
                            }

                        }
                        else
                        {
                            $message ='📌 Hasil Pencarian <b>Lainnya</b> : ' . chr(10) .chr(10);

                            foreach ($response['data'][1] as $item)
                            {
                                $url_link = explode("-",$item['rl_date']);
                                $link = 'https://ntb.bps.go.id/news/'.$url_link[0].'/'.$url_link[1].'/'.$url_link[2].'/'.$item['news_id'].'/bpsntb.html';
                                $message .= '🟤 Judul : <b>'.$item['title'].'</b>' .chr(10);
                                $message .= '🟤 Tanggal : <b>'.\Carbon\Carbon::parse($item['rl_date'])->format('d M Y').'</b> 🔺 <a href="'.$link.'">Link</a>' .chr(10);
                                $message .='--------------------------------------------'.chr(10);
                            }
                            $respon = Telegram::sendMessage([
                                'chat_id' => $this->chat_id,
                                'text' => $message,
                                'parse_mode'=> 'HTML',
                                'disable_web_page_preview'=> true,
                            ]);
                            $messageId = $respon->getMessageId();

                        }
                    }
                    else
                    {
                        $message ='📌 <b>Pencarian Lainnya</b> yang anda cari tidak tersedia' .chr(10);
                        $message .= '<i>Ulangi pencarian lainnya</i>' .chr(10);
                        $respon = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'disable_web_page_preview'=> true,
                        ]);
                        $messageId = $respon->getMessageId();

                    }
                    $this->CariLainnya();
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

                        $message ='🟢 Pesan : <b>'.$dt->isi_pesan.'</b>'.chr(10);
                        $message .='⏱ Tanggal : '.Carbon::parse($dt->created_at)->isoFormat('D MMMM Y H:mm:ss').chr(10);
                        $message .='--------------------------'.chr(10);
                        $message .='🔹 Balasan : <b>'.$pesan.'</b>'.chr(10);
                        //kirim ke pengunjung yg kirim pesan
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_konsultasi,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $dt->chatid,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        //batas kirimnya
                        //$this->KirimByAdmin($pesan,$dt->chatid, true);
                        //$this->MenuKonsultasi(true,true);
                        //pesan diterukan ke admin yg balas

                        $message ='';
                        $message .='🟢 Pesan anda' .chr(10);
                        $message .='-------------------------------------------' .chr(10);
                        $message .='<b>'.$pesan.'</b>' .chr(10);
                        $message .='-------------------------------------------' .chr(10);
                        $message .='🟢 sudah terkirim ke <b>'.$d_pengunjung->nama.' ('.$d_pengunjung->username.')</b>' .chr(10);
                        //kirimi notifikasi ke admin yg balas pesan
                        $reply_markup = Keyboard::make([
                            'keyboard' => $this->keyboard_konsultasi,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true
                        ]);
                        $response = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'reply_markup' => $reply_markup
                        ]);
                        $messageId = $response->getMessageId();
                        //batas kirim
                        //$this->KirimPesan($message,true);
                        //forward ke admin yg lain jg
                        //pilih forward ke admin lain
                        $message_ke_admin_lain ='';
                        $message_ke_admin_lain .='🟢 Pesan dari admin ('.$data_admin->nama.'):' .chr(10);
                        $message_ke_admin_lain .='-------------------------------------------' .chr(10);
                        $message_ke_admin_lain .='<b>'.$pesan.'</b>' .chr(10);
                        $message_ke_admin_lain .='-------------------------------------------' .chr(10);
                        $message_ke_admin_lain .='🟢 sudah terkirim ke <b> '.$d_pengunjung->nama.' ('.$d_pengunjung->username.')</b>' .chr(10) .chr(10);

                        $cek_admin_online = User::where([['chatid_tg','<>',''],['status_online','=','1']])->count();
                        if ($cek_admin_online > 0)
                        {
                            //kirim forward pesan
                            $dataadmin = User::where([['chatid_tg','<>',''],['status_online','=','1']])->get();
                            foreach ($dataadmin as $item) {
                                $this->chat_id = $item->chatid_tg;
                                if ($item->chatid_tg != $data_admin->chatid_tg)
                                {
                                    //$this->KirimPesan($message_ke_admin_lain,true);
                                    //kirirm ke admin lagin
                                    $reply_markup = Keyboard::make([
                                        'keyboard' => $this->keyboard_konsultasi,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard' => true
                                    ]);
                                    $response = Telegram::sendMessage([
                                        'chat_id' => $item->chatid_tg,
                                        'text' => $message_ke_admin_lain,
                                        'parse_mode'=> 'HTML',
                                        'reply_markup' => $reply_markup
                                    ]);
                                    $messageId = $response->getMessageId();
                                    //batasannya
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

                        //$this->KirimPesan($message,true);
                        $respon = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML',
                            'disable_web_page_preview'=> true,
                        ]);
                        $messageId = $respon->getMessageId();
                        //$this->MenuKonsultasi();
                    }
                }
                elseif ($tg->command == 'FeedbackBintang')
                {
                    LogPengunjung::create([
                        'username' => $this->username,
                        'chatid' => $this->chat_id,
                        'command' => 'FeedbackSaran',
                        'msg_id' => $this->message_id
                    ]);
                    //input nilai sesuai pilihan
                    //cek dulu apakah sudah pernah ngisi
                    //kalo sudah pernah mengisi langsung update saja
                    if ($this->text == '1⭐️')
                    {
                        $nilai = 1;
                    }
                    elseif ($this->text == '2⭐️')
                    {
                        $nilai = 2;
                    }
                    elseif ($this->text == '3⭐️')
                    {
                        $nilai = 3;
                    }
                    elseif ($this->text == '4⭐️')
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
                    $message .= '🟢 Terimakasih atas penilaian Bapak/Ibu untuk perbaikan <b>Teledata</b> Kedepan' .chr(10);
                    $message .= '-----------------------------------------------------'.chr(10);
                    $message .= '<i>Silakan masukkan komentar Bapak/Ibu tentang <b>TeleDATA</b></i> : '.chr(10);
                    $reply_markup = Keyboard::make([
                        'keyboard' => $this->keyboard_saran_feedback,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]);
                    $response = Telegram::sendMessage([
                        'chat_id' => $this->chat_id,
                        'text' => $message,
                        'parse_mode'=> 'HTML',
                        'reply_markup' => $reply_markup
                    ]);
                    $messageId = $response->getMessageId();
                }
                elseif ($tg->command == 'FeedbackSaran')
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
                    $message .='🌀 Masukkan Bapak/Ibu sudah tersimpan' . chr(10);
                    $message .='---------------------------------------------' . chr(10);
                    $message .= '<i>Terimakasih atas masukkan Bapak/Ibu untuk perbaikan <b>TeleDATA</b></i>'.chr(10);
                    $response = Telegram::sendMessage([
                        'chat_id' => $this->chat_id,
                        'text' => $message,
                        'parse_mode'=> 'HTML',
                    ]);
                    $messageId = $response->getMessageId();
                    $this->MenuFeedback();
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
                            $respon = Telegram::forwardMessage([
                                'chat_id' => $item->chatid_tg,
                                'from_chat_id' => $this->chat_id,
                                'message_id' => $this->message_id
                            ]);
                            LogPengunjung::create([
                                'username' => $item->user_tg,
                                'chatid' => $item->chatid_tg,
                                'command' => 'ReplyByAdmin',
                                'msg_id' => $this->message_id
                            ]);
                        }

                    }
                        $message ='';
                        $message .='📦 Pesan anda' .chr(10);
                        $message .='-------------------------------------'.chr(10);
                        $message .='<b>'.$this->text.'</b>'.chr(10);
                        $message .='-------------------------------------'.chr(10);
                        $message .='🟢 berhasil disimpan'.chr(10);

                        /*
                        $tg->command = 'showMenu';
                        $tg->update();
                        */
                        $respon = Telegram::sendMessage([
                            'chat_id' => $this->chat_id,
                            'text' => $message,
                            'parse_mode'=> 'HTML'
                        ]);
                        $this->MenuKonsultasi();
                }
                else
                {
                    $message ='';
                    $message .='⚠️ Perintah tidak dikenali. ⚠️ <b>Silakan pilih menu dibawah ini</b>' . chr(10) .chr(10);
                    $tg->command = 'showMenu';
                    $tg->update();
                    $reply_markup = Keyboard::make([
                        'keyboard' => $this->keyboard_utama,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]);
                    $respon = Telegram::sendMessage([
                        'chat_id' => $this->chat_id,
                        'text' => $message,
                        'parse_mode'=> 'HTML',
                        'reply_markup' => $reply_markup
                    ]);
                    $messageId = $respon->getMessageId();
                    $this->AwalStart();
                }
            }
            else
            {
                //sama sekali belum ada di log / pengunjung sudah selesai
                $message ='';
                $message .='⚠️ Perintah tidak dikenali. ⚠️ <b>Silakan pilih menu dibawah ini</b>' . chr(10);
                LogPengunjung::create([
                    'username' => $this->username,
                    'chatid' => $this->chat_id,
                    'command' => 'showMenu',
                    'msg_id'=> $this->message_id
                ]);
                $respon = Telegram::sendMessage([
                    'chat_id' => $this->chat_id,
                    'text' => $message,
                    'parse_mode'=> 'HTML',
                    'disable_web_page_preview'=> true,
                ]);
                $messageId = $respon->getMessageId();
                $this->AwalStart();
            }
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
    public function Selesai()
    {
        LogPengunjung::create([
            'username' => $this->username,
            'chatid' => $this->chat_id,
            'command' => __FUNCTION__,
            'msg_id' => $this->message_id
        ]);
        $message = "🙏 <b>Terimakasih Telah Menggunakan Layanan Kami</b>" .chr(10);
        $count = LogPengunjung::where('chatid', $this->chat_id)->count();
        if ($count > 0)
        {
            LogPengunjung::where('chatid', $this->chat_id)->delete();
        }
        $reply_markup = Keyboard::make([
            'remove_keyboard' => true,
        ]);
        $response = Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode'=> 'HTML',
            'reply_markup' => $reply_markup
        ]);
        $messageId = $response->getMessageId();
    }

}
