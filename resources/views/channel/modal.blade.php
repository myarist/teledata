<!-- modal kirim pesan -->
<div id="KirimPesanModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Kirim Pesan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <!--isi modal-->
                <form class="m-t-10" name="formKirimPesan" method="post" action="{{route('channel.kirimpesan')}}">
                    @csrf
                   <div class="form-group">
                    <label for="peg_nipbps">PESAN</label>
                    <div class="controls">
                    <textarea name="pesan" id="pesan_channel" class="form-control" rows="5"></textarea>
                    </div>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">CLOSE</button>
                   <button type="submit" class="btn btn-success waves-effect waves-light">KIRIM</button>
               </div>

           </form>
        </div>
    </div>
</div>
<!-- /.modal kirim pesan -->
