@extends('layouts.default')
@section('konten')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor">Konsultasi Online</h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
        <div class="d-flex justify-content-end align-items-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Starter Page</li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 col-sm-12">
        @if (Session::has('message'))
        <div class="alert alert-{{ Session::get('message_type') }}" id="waktu2" style="margin-top:10px;">{{ Session::get('message') }}</div>
        @endif
    </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Start Page Content -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-12">
        <div class="card m-b-0">
            <!-- .chat-row -->
            <div class="chat-main-box">
                <!-- .chat-left-panel -->
                <div class="chat-left-aside">
                    <div class="open-panel"><i class="ti-angle-right"></i></div>
                    <div class="chat-left-inner">
                        <div class="p-3 b-b">
                            <h4 class="box-title">Daftar Nama</h4>
                        </div>
                        <ul class="chatonline style-none ">
                            @foreach ($dataUser as $item)
                            <li>
                                <a href="{{route('konsultasi.chat',$item->chatid)}}"><span>{{$item->nama}} <small class="text-success">{{$item->username}}</small></span></a>
                            </li>
                            @endforeach
                            <li class="p-20"></li>
                        </ul>
                    </div>
                </div>
                <!-- .chat-left-panel -->
                <!-- .chat-right-panel -->
                <div class="chat-right-aside">
                    <div class="chat-main-header">
                        <div class="p-3 b-b">
                            <h4 class="box-title">Chat Message</h4>
                        </div>
                    </div>
                    <div class="chat-rbox" id="slimtest1" style="height: 350px;">
                        <ul class="chat-list p-3">
                            @foreach ($dataChat as $item)
                                @if ($item->chat_admin == 0)
                                    <!--chat Row -->
                                    <li>
                                        <div class="chat-content">
                                            <h5>{{$item->dp_nama}}</h5>
                                            <div class="box bg-light-info">
                                                {!! html_entity_decode($item->isi_pesan) !!}
                                            </div>
                                            <div class="chat-time">{{\Carbon\Carbon::parse($item->created_at)->format('j M Y h:i a')}}</div>
                                        </div>
                                    </li>
                                    <!--chat Row -->
                                @else
                                    <!--chat Row -->
                                    <li class="reverse">
                                        <div class="chat-content">
                                            <h5>Admin</h5>
                                            <div class="box bg-light-inverse">
                                                {!! html_entity_decode($item->isi_pesan) !!}
                                            </div>
                                            <div class="chat-time">{{\Carbon\Carbon::parse($item->created_at)->format('j M Y h:i a')}}</div>
                                        </div>
                                        
                                    </li>
                                    <!--chat Row -->
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-body border-top">
                        <form name="KirimPesan" method="POST" action="{{route('konsultasi.reply')}}">
                            @csrf
                        <div class="row">
                            <div class="col-8">
                                <textarea placeholder="Tulis pesan disini" name="pesan" id="pesan" class="form-control border-0" required></textarea>
                            </div>
                            <div class="col-4 text-right">
                                <button type="submit" class="btn btn-info btn-circle btn-lg"><i class="fas fa-paper-plane"></i> </button>
                            </div>
                        </div>
                        <input type="hidden" name="chatid" id="chatid" value="{{$chatid}}" />
                    </form>
                    </div>
                </div>
                <!-- .chat-right-panel -->
            </div>
            <!-- /.chat-row -->
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End PAge Content -->
<!-- ============================================================== -->
@endsection

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Page CSS -->
<link href="{{asset('dist/css/pages/chat-app-page.css')}}" rel="stylesheet">
@endsection

@section('js')
   <!-- slimscrollbar scrollbar JavaScript -->
   <script src="{{asset('dist/js/perfect-scrollbar.jquery.min.js')}}"></script>
   <script>
    $('#slimtest1, #slimtest2, #slimtest3, #slimtest4').perfectScrollbar();
   </script>  
@endsection