<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\LogPengunjung;
use App\DataPengunjung;
use Carbon\Carbon;
use Telegram\Bot\Keyboard\Keyboard;
use App\LogPesan;
use DB;

class AdminController extends Controller
{
    //
    public function list()
    {
        $data_admin = User::get();

        return view('admin.list',['dataAdmin'=>$data_admin]);
    }
}
