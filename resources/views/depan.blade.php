@extends('layouts.default')
@section('konten')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor">Dashboard TeleData</h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
        <div class="d-flex justify-content-end align-items-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- Start Page Content -->
<!-- ============================================================== -->
<div class="row">
    <!-- Column -->
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex m-b-40 align-items-center no-block">
                    <h5 class="card-title ">JUMLAH PENGUNJUNG</h5>

                </div>
                <div id="jumlah-pengunjung" style="height: 320px;"></div>
            </div>
        </div>
    </div>
    <!-- Column -->
    <div class="col-lg-4 col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">PENCARIAN DATA</h4>
                <div id="jumlah-pencarian"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-2 col-md-12">
        <div class="card bg-cyan text-white">
            <div class="card-body ">
                <div class="row">
                    <div class="col-12">
                        <h3 class="text-right"><i>Feedback</i></h3>
                        <h1>{{number_format($dataFeedback->average('nilai_feedback'),2, '.', '')}}</h1>
                        <div>
                            {{--Start Rating--}}
                            @for ($i = 0; $i < 6; $i++)
                            @if (floor($dataFeedback->average('nilai_feedback')) - $i >= 1)
                                {{--Full Start--}}
                                <i class="fa fa-star text-white"> </i>
                            @elseif ($dataFeedback->average('nilai_feedback') - $i > 0)
                                {{--Half Start--}}
                                <i class="fas fa-star-half-alt text-white"></i>
                            @else
                                {{--Empty Start--}}
                                <i class="far fa-star text-white"> </i>
                            @endif
                            @endfor
                            {{--End Rating--}}
                        </div>
                        <div class="text-white"><i class="fas fa-user"></i> {{$dataFeedback->count()}} total</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div id="myCarouse2" class="carousel slide" data-ride="carousel">
                    <!-- Carousel items -->
                    <div class="carousel-inner">
                        @foreach ($dataFeedback as $item)
                        <div class="carousel-item @if ($loop->first) active @endif">
                            <h4 class="cmin-height"><i>{{$item->isi_feedback}}</i></h4>
                            <div class="d-flex no-block">

                            <span class="m-t-20">
                                <h4 class="text-white m-b-0">{{$item->Pengunjung->nama}}</h4>
                                @for ($i = 1; $i < 6; $i++)
                                    @if ($i <= $item->nilai_feedback)
                                        <span class="fa fa-star"></span>
                                    @else
                                        <span class="far fa-star"></span>
                                    @endif
                                @endfor
                                <p class="text-white">{{\Carbon\Carbon::parse($item->created_at)->isoFormat('D MMMM Y H:m')}}</p>


                            </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">JUMLAH KONSULTASI</h4>
                <div class="stats-row float-right">
                    <div class="stat-item">
                        <h6>TOTAL</h6>
                        <b>{{$dataKonsul->count()}}</b></div>
                </div>
            </div>
            <div id="jumlah-konsultasi" class="sparkchart"></div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End PAge Content -->
<!-- ============================================================== -->
@endsection

@section('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- chartist CSS -->
<link href="{{asset('assets/node_modules/morrisjs/morris.css')}}" rel="stylesheet">
<!--alerts CSS -->
<link href="{{asset('assets/node_modules/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
@endsection

@section('js')
    <!-- Sweet-Alert  -->
    <script src="{{asset('assets/node_modules/sweetalert2/dist/sweetalert2.all.min.js')}}"></script>

    <!--morris JavaScript -->
    <script src="{{asset('assets/node_modules/raphael/raphael-min.js')}}"></script>
    <script src="{{asset('assets/node_modules/morrisjs/morris.min.js')}}"></script>
    <script src="{{asset('assets/node_modules/jquery-sparkline/jquery.sparkline.min.js')}}"></script>
    @include('jsdepan')
@endsection
