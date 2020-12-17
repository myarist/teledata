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


class FeedbackController extends Controller
{
    //
    public function List()
    {
        $feed = LogFeedback::orderBy('updated_at','desc')->get();
        return view('feedback.index',['dataFeedback'=>$feed]);
    }
}
