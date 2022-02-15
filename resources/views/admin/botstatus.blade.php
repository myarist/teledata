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
                @if ($respon['ok'] == 'true')
                    <dl>
                        <dt>url</dt>
                        <dd>{{$respon['result']['url']}}</dd>
                        <dt>has_custom_certificate</dt>
                        <dd>{{$respon['result']['has_custom_certificate']}}</dd>
                        <dt>pending_update_count</dt>
                        <dd>{{$respon['result']['pending_update_count']}}</dd>
                        <dt>max_connections</dt>
                        <dd>{{$respon['result']['max_connections']}}</dd>
                        <dt>ip_address</dt>
                        <dd>{{$respon['result']['ip_address']}}</dd>
                    </dl>
                @else
                    ERROR
                @endif
               
            </div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End PAge Content -->
<!-- ============================================================== -->

@endsection



