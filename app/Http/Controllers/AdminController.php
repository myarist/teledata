<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\LogPengunjung;
use App\DataPengunjung;
use Carbon\Carbon;
use Telegram\Bot\Keyboard\Keyboard;
use App\LogPesan;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\LogFeedback;

class AdminController extends Controller
{
    //
    public function list()
    {
        $data_admin = User::get();

        return view('admin.list',['dataAdmin'=>$data_admin]);
    }
    public function FlagAdmin(Request $request)
    {
        $count = User::where('id','=',$request->id)->count();
        $arr = array(
            'status'=>false,
            'hasil'=>'Data admin tidak tersedia'
        );
        if ($count>0)
        {
            $data = User::where('id','=',$request->id)->first();
            if ($request->flag==1)
            {
                $flag_aktif = 0;
                $flag_nama = 'NONAKTIF';
            }
            else 
            {
                $flag_aktif = 1;
                $flag_nama = 'AKTIF';
            }
            $data->aktif = $flag_aktif;
            $data->update();
            $arr = array(
                'status'=>true,
                'hasil'=>'Flag admin '.$data->nama.' sudah diubah ke '. $flag_nama
            );
        }
        return Response()->json($arr);
    }
    public function SimpanAdmin(Request $request)
    {
        //dd($request->all());
        //cek dulu username yg sudah ada
        $cek_count = User::where('username',$request->admin_username)->count();
        if ($cek_count > 0)
        {
            //sudah ada
            $pesan_error = '(ERROR) username '. $request->admin_username .' sudah ada yang menggunakan';
            $pesan_warna = 'danger';
        }
        else
        {
            $data = new User();
            $data->nama = $request->admin_nama;
            $data->username = $request->admin_username;
            $data->password = bcrypt($request->admin_password);
            $data->email = $request->admin_email;
            $data->user_tg = $request->user_tg;
            $data->save();

            $pesan_error = '(SUKSES) Admin berhasil di simpan';
            $pesan_warna = 'success';
        }
        Session::flash('message', $pesan_error);
        Session::flash('message_type', $pesan_warna);
        return redirect()->route('admin.list');
    }
    public function cariadmin($id)
    {
        //semua admin
        $count = User::where('id',$id)->count();
        $arr = array('status'=>false,'hasil'=>'Data admin tidak tersedia');
        if ($count > 0) {
            //data admin ada
            $data = User::where('id',$id)->first();
            $hasil = array(
                    'admin_id'=>$data->id,
                    'admin_nama'=>$data->nama,
                    'admin_username'=>$data->username,
                    'admin_email'=>$data->email,
                    'admin_usertg'=>$data->user_tg,
                    'admin_chatid'=>$data->chatid_tg,
                    'admin_lastlogin'=>$data->lastlogin,
                    'admin_lastip'=>$data->lastip,
                    'admin_aktif'=>$data->aktif,
                    'admin_created_at'=>$data->created_at,
                    'admin_updated_at'=>$data->updated_at,
                );
            $arr = array(
                'status'=>true,
                'hasil' => $hasil
            );
        }
        return Response()->json($arr);
    }
    public function UpdateAdmin(Request $request)
    {
        //dd($request->all());
        $cek_count = User::where('id',$request->id)->count();
        if ($cek_count > 0)
        {
            //admin ada
            $data = User::where('id',$request->id)->first();
            $data->nama = $request->admin_nama;
            $data->email = $request->admin_email;
            $data->user_tg = $request->user_tg;
            $data->update();
            $pesan_error = '(SUKSES) Admin berhasil di update';
            $pesan_warna = 'success';
        }
        else
        {
            $pesan_error = '(ERROR) data admin tidak bisa di update';
            $pesan_warna = 'danger';
        }
        Session::flash('message', $pesan_error);
        Session::flash('message_type', $pesan_warna);
        return redirect()->route('admin.list');
    }
    public function GantiPasswordAdmin(Request $request)
    {
        //dd($request->all());
        //cek dulu admin sudah ada
        $cek_count = User::where('id',$request->id)->count();
        if ($cek_count > 0)
        {
            //admin ada
            $data = User::where('id',$request->id)->first();
            $data->password = bcrypt($request->admin_password);
            $data->update();
            $pesan_error = '(SUKSES) Admin berhasil di simpan';
            $pesan_warna = 'success';
        }
        else
        {
            $pesan_error = '(ERROR) Password admin tidak bisa di ganti';
            $pesan_warna = 'danger';
        }
        Session::flash('message', $pesan_error);
        Session::flash('message_type', $pesan_warna);
        return redirect()->route('admin.list');
    }
    public function HapusAdmin(Request $request)
    {
        $count = User::where('id',$request->id)->count();
        $arr = array(
            'status'=>false,
            'hasil'=>'Data admin tidak tersedia'
        );
        if ($count>0)
        {
            $data = User::where('id',$request->id)->first();
            $nama = $data->nama;
            $data->delete();
            $arr = array(
                'status'=>true,
                'hasil'=>'Data '.$nama.' berhasil dihapus'
            );
        }
        return Response()->json($arr);
    }
    public function StatusOnline(Request $request)
    {
        $count = User::where('id','=',$request->id)->count();
        $arr = array(
            'status'=>false,
            'hasil'=>'Data admin tidak tersedia'
        );
        if ($count>0)
        {
            $data = User::where('id',$request->id)->first();
            if ($request->flag==1)
            {
                $status_online = 0;
                $flag_nama = 'OFFLINE';
            }
            else 
            {
                $status_online = 1;
                $flag_nama = 'ONLINE';
            }
            $data->status_online = $status_online;
            $data->update();
            $arr = array(
                'status'=>true,
                'hasil'=>'Status Online sudah diubah ke '. $flag_nama
            );
        }
        return Response()->json($arr);
    }
}
