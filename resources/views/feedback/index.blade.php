@extends('layouts.default')
@section('konten')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor">Daftar Feedback</h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
        <div class="d-flex justify-content-end align-items-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Daftar Feedback</li>
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
                    <div class="col-lg-3 col-md-12 border-right p-l-0">
                        <center class="m-t-30 m-b-40 p-t-20 p-b-20">
                            <font class="display-3">{{number_format($dataFeedback->average('nilai_feedback'),2, '.', '')}}</font>
                            <div class="m-b-10">
                                {{--Start Rating--}}
                                @for ($i = 0; $i < 6; $i++)
                                @if (floor($dataFeedback->average('nilai_feedback')) - $i >= 1)
                                    {{--Full Start--}}
                                    <i class="fa fa-star text-warning"> </i>
                                @elseif ($dataFeedback->average('nilai_feedback') - $i > 0)
                                    {{--Half Start--}}
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                @else
                                    {{--Empty Start--}}
                                    <i class="far fa-star text-warning"> </i>
                                @endif
                                @endfor
                                {{--End Rating--}}
                            </div>
                            <h6 class="text-muted"><i class="fas fa-user"></i> {{$dataFeedback->count()}} total</h6>

                        </center>
                        <hr>
                    </div>
                    <div class="col-9">
                        <div class="row">
                            <div class="col-lg-1 col-md-2">
                                <span class="float-right">
                                    5 <span class="fa fa-star text-warning"></span>
                                </span>
                            </div>
                            <div class="col-lg-11 col-md-10">
                                <div class="progress ">
                                    <div class="progress-bar bg-info wow animated progress-animated" style="width: {{number_format(($dataFeedback->where('nilai_feedback','5')->count()/$dataFeedback->count())*100,2,".",",")}}%; height:20px;" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-10">
                            <div class="col-lg-1 col-md-2">
                                <span class="float-right">
                                    4 <span class="fa fa-star text-warning"></span>
                                </span>
                            </div>
                            <div class="col-lg-11 col-md-10">
                                <div class="progress ">
                                    <div class="progress-bar bg-warning wow animated progress-animated" style="width: {{number_format(($dataFeedback->where('nilai_feedback','4')->count()/$dataFeedback->count())*100,2,".",",")}}%; height:20px;" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-10">
                            <div class="col-lg-1 col-md-2">
                                <span class="float-right">
                                    3 <span class="fa fa-star text-warning"></span>
                                </span>
                            </div>
                            <div class="col-lg-11 col-md-10">
                                <div class="progress ">
                                    <div class="progress-bar bg-primary wow animated progress-animated" style="width: {{number_format(($dataFeedback->where('nilai_feedback','3')->count()/$dataFeedback->count())*100,2,".",",")}}%; height:20px;" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-10">
                            <div class="col-lg-1 col-md-2">
                                <span class="float-right">
                                    2 <span class="fa fa-star text-warning"></span>
                                </span>
                            </div>
                            <div class="col-lg-11 col-md-10">
                                <div class="progress ">
                                    <div class="progress-bar bg-inverse wow animated progress-animated" style="width: {{number_format(($dataFeedback->where('nilai_feedback','2')->count()/$dataFeedback->count())*100,2,".",",")}}%; height:20px;" role="progressbar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-10">
                            <div class="col-lg-1 col-md-2">
                                <span class="float-right">
                                    1 <span class="fa fa-star text-warning"></span>
                                </span>
                            </div>
                            <div class="col-lg-11 col-md-10">
                                <div class="progress ">
                                    <div class="progress-bar bg-danger wow animated progress-animated" style="width: {{number_format(($dataFeedback->where('nilai_feedback','1')->count()/$dataFeedback->count())*100,2,".",",")}}%; height:20px;" role="progressbar" aria-valuenow="0%" aria-valuemin="0%" aria-valuemax="100%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="pengunjung" class="table table-bordered table-hover table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Nilai</th>
                                <th>Tanggal</th>
                                <th>Pesan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataFeedback as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td><strong>{{$item->Pengunjung->nama}}</strong></td>
                                    <td>
                                        @for ($i = 1; $i < 6; $i++)
                                            @if ($i <= $item->nilai_feedback)
                                                <span class="fa fa-star text-warning"></span>
                                            @else
                                                <span class="fa fa-star"></span>
                                            @endif
                                        @endfor
                                    </td>
                                    <td>
                                        {{\Carbon\Carbon::parse($item->updated_at)->isoFormat('D MMMM Y H:m')}}
                                    </td>
                                    <td><i>{{$item->isi_feedback}}</i></td>
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
@include('pengunjung.modal')
@endsection

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- chartist CSS -->
<link href="{{asset('assets/node_modules/morrisjs/morris.css')}}" rel="stylesheet">
<!--alerts CSS -->
<link href="{{asset('assets/node_modules/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="{{asset('assets/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('assets/node_modules/datatables.net-bs4/css/responsive.dataTables.min.css')}}">
@endsection

@section('js')
    <!-- Sweet-Alert  -->
    <script src="{{asset('assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js')}}"></script>
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
            $('#pengunjung').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'pdf', 'print'
                ],
               "displayLength": 30,
                
            });
            $('.buttons-copy, .buttons-csv, .buttons-print, .buttons-pdf, .buttons-excel').addClass('btn btn-primary mr-1');
        });

    </script>
    
    @include('feedback.js')
@endsection