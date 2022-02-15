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
                @if ($respon)
                <dl>
                    <dt>#ID</dt>
                    <dd>{{$respon['id']}}</dd>
                    <dt>is_bot</dt>
                    <dd>{{$respon['is_bot']}}</dd>
                    <dt>first_name</dt>
                    <dd>{{$respon['first_name']}}</dd>
                    <dt>username</dt>
                    <dd>{{$respon['username']}}</dd>
                    <dt>can_join_groups</dt>
                    <dd>{{$respon['can_join_groups']}}</dd>
                    <dt>can_read_all_group_messages</dt>
                    <dd>{{$respon['can_read_all_group_messages']}}</dd>
                    <dt>supports_inline_queries</dt>
                    <dd>{{$respon['supports_inline_queries']}}</dd>
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



