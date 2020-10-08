@extends('layouts.default')
@section('konten')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor">Administrasi User</h4>
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
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12">
                        <button class="btn btn-success float-right" data-toggle="modal" data-target="#TambahAdminModal"><i class="fas fa-plus" data-toggle="tooltip" title="Tambah Admin"></i> TAMBAH ADMIN</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tabeladmin" class="table table-bordered table-hover table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Userlogin</th>
                                <th>Email</th>
                                <th>User Telegram</th>
                                <th>ID Telegram</th>
                                <th>Lastlogin</th>
                                <th>Aktif</th>
                                <th width="12%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataAdmin as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$item->nama}}</td>
                                    <td>{{$item->username}}</td>
                                    <td>{{$item->email}}</td>
                                    <td>{{$item->user_tg}}</td>
                                    <td>{{$item->chatid_tg}}</td>
                                    <td align="center">
                                        @if ($item->lastip)
                                        {{$item->lastip}} <br />({{Carbon\Carbon::parse($item->lastlogin)->diffForHumans()}})
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->aktif==1)
                                        <span class="label label-rounded label-success">AKTIF</span>
                                        @else 
                                        <span class="label label-rounded label-danger">NONAKTIF</span>
                                        @endif
                                    </td>
                                    <td>
                                        
                                        <button class="btn btn-sm btn-success" data-id="{{$item->id}}" data-toggle="modal" data-target="#EditAdminModal"><i class="fas fa-pencil-alt" data-toggle="tooltip" title="Edit Admin"></i></button>
                                        @if ($item->username != 'admin' or Auth::user()->username=='admin')
                                        <button class="btn btn-sm btn-warning flagadmin" data-id="{{$item->id}}" data-flag="{{$item->aktif}}"><i class="fas fa-flag" data-toggle="tooltip" title="Ubah Flag"></i></button>
                                        <button class="btn btn-sm btn-info" data-id="{{$item->id}}" data-toggle="modal" data-target="#GantiPasswordModal"><i class="fas fa-key" data-toggle="tooltip" title="Ganti Password {{$item->nama}}"></i></button>
                                        <button class="btn btn-sm btn-danger hapusadmin" data-id="{{$item->id}}" data-nama="{{$item->nama}}"><i class="fas fa-trash" class="fas fa-key" data-toggle="tooltip" title="Hapus Admin"></i></button>
                                        @endif
                                        
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End PAge Content -->
<!-- ============================================================== -->
@include('admin.modal')
@endsection

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!--alerts CSS -->
<link href="{{asset('assets/node_modules/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{asset('assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css')}}">
@endsection

@section('js')
    <!-- This is data table -->
    <script src="{{asset('assets/node_modules/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js')}}"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        $(function () {
            $('#tabeladmin').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    
                ],
                responsive: true,
                "displayLength": 30,
                
            });
            $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary mr-1');
        });

    </script>
    <!-- Sweet-Alert  -->
    <script src="{{asset('assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js')}}"></script>
    <!--end - This is for export functionality only-->
    <script src="{{asset('dist/js/pages/validation.js')}}"></script>
    @include('admin.js')
@endsection