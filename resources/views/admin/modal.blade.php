<!-- modal tambah admin -->
<div id="TambahAdminModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Admin</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <!--isi modal-->
                <form class="m-t-10" name="formKirimPesan" method="post" action="{{route('admin.simpan')}}">
                    @csrf
                    @include('admin.form')
                   
            </div>
               
            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">CLOSE</button>
                <button type="submit" class="btn btn-success waves-effect waves-light">SIMPAN</button>
            </div>
               
                </form>
        </div>
    </div>
</div>
<!-- /.modal tambah admin -->

<!-- modal edit admin -->
<div id="EditAdminModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Admin</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <!--isi modal-->
                <form class="m-t-10" name="formEditAdmin" method="post" action="{{route('admin.update')}}">
                    @csrf
                    @include('admin.formedit')
                   
            </div>
               
            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">CLOSE</button>
                <button type="submit" class="btn btn-success waves-effect waves-light">UPDATE</button>
            </div>
               
                </form>
        </div>
    </div>
</div>
<!-- /.modal edit admin -->
<!-- modal password admin -->
<div id="GantiPasswordModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Password</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <!--isi modal-->
                <form class="m-t-10" name="formEditPassword" method="post" action="{{route('admin.gantipassword')}}">
                    @csrf
                    <dl class="row">
                        <dt class="col-sm-4">Nama Lengkap</dt>
                        <dd class="col-sm-8"><span id="admin_nama"></span></dd>
                        <dt class="col-sm-4">Username</dt>
                        <dd class="col-sm-8"><span id="admin_username"></span></dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><span id="admin_email"></span></dd>
                        <dt class="col-sm-4">User Telegram</dt>
                        <dd class="col-sm-8"><span id="admin_usertg"></span></dd>
                    </dl>
                   @include('admin.formgantipass')
            </div>
               
            <div class="modal-footer">
                <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">CLOSE</button>
                <button type="submit" class="btn btn-success waves-effect waves-light">GANTI PASSWORD</button>
            </div>
               
                </form>
        </div>
    </div>
</div>
<!-- /.modal password admin -->