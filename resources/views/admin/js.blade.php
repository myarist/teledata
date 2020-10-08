<script>
$(".flagadmin").click(function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var flag = $(this).data('flag');
    if (flag == 1)
    {
        var flagtext = 'NONAKTIF';
    }
    else
    {
        var flagtext = 'AKTIF';
    }
    
    Swal.fire({
                title: 'Edit Flag?',
                text: "Flag Admin akan diset ke "+flagtext,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ubah'
            }).then((result) => {
                if (result.value) {
                    //response ajax disini
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url : '{{route('admin.flag')}}',
                        method : 'post',
                        data: {
                            id: id,
                            flag: flag 
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(data){
                            if (data.status == true)
                            {
                                Swal.fire(
                                    'Berhasil!',
                                    ''+data.hasil+'',
                                    'success'
                                ).then(function() {
                                    location.reload();
                                });
                            }
                            else
                            {
                                Swal.fire(
                                    'Error!',
                                    ''+data.hasil+'',
                                    'danger'
                                ); 
                            }
                            
                        },
                        error: function(){
                            Swal.fire(
                                'Error',
                                'Koneksi Error',
                                'danger'
                            );
                        }

                    });
                   
                }
            })
});
//edit admin
$('#EditAdminModal').on('show.bs.modal', function (event) {
var button = $(event.relatedTarget) // Button that triggered the modal
var id = button.data('id') // Extract info from data-* attributes
$.ajax({
        url : '{{route('admin.cari','')}}/'+id,
        method : 'get',
        cache: false,
        dataType: 'json',
        success: function(data){
           if (data.status==true)
           {
            $('#EditAdminModal .modal-body #id').val(id);
            $('#EditAdminModal .modal-body #admin_nama').val(data.hasil.admin_nama);
            $('#EditAdminModal .modal-body #admin_username').val(data.hasil.admin_username);
            $('#EditAdminModal .modal-body #admin_email').val(data.hasil.admin_email);
            $('#EditAdminModal .modal-body #user_tg').val(data.hasil.admin_usertg);
           }
           else
           {
               alert(data.hasil);
           }
        },
        error: function(){
            alert("error");
        }

    });   
});
//batas edit admin
//ganti password admin
$('#GantiPasswordModal').on('show.bs.modal', function (event) {
var button = $(event.relatedTarget) // Button that triggered the modal
var id = button.data('id') // Extract info from data-* attributes
$.ajax({
        url : '{{route('admin.cari','')}}/'+id,
        method : 'get',
        cache: false,
        dataType: 'json',
        success: function(data){
           if (data.status==true)
           {
            $('#GantiPasswordModal .modal-body #id').val(id);
            $('#GantiPasswordModal .modal-body #admin_nama').text(data.hasil.admin_nama);
            $('#GantiPasswordModal .modal-body #admin_username').text(data.hasil.admin_username);
            $('#GantiPasswordModal .modal-body #admin_email').text(data.hasil.admin_email);
            $('#GantiPasswordModal .modal-body #admin_usertg').text(data.hasil.admin_usertg);
           }
           else
           {
               alert(data.hasil);
           }
        },
        error: function(){
            alert("error");
        }

    });   
});
//batas password admin
//hapus admin
$(".hapusadmin").click(function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var nama = $(this).data('nama');    
    Swal.fire({
                title: 'Akan dihapus?',
                text: "Data admin "+nama+" akan dihapus permanen",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Hapus'
            }).then((result) => {
                if (result.value) {
                    //response ajax disini
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url : '{{route('admin.hapus')}}',
                        method : 'post',
                        data: {
                            id: id
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(data){
                            if (data.status == true)
                            {
                                Swal.fire(
                                    'Berhasil!',
                                    ''+data.hasil+'',
                                    'success'
                                ).then(function() {
                                    location.reload();
                                });
                            }
                            else
                            {
                                Swal.fire(
                                    'Error!',
                                    ''+data.hasil+'',
                                    'danger'
                                ); 
                            }
                            
                        },
                        error: function(){
                            Swal.fire(
                                'Error',
                                'Koneksi Error',
                                'danger'
                            );
                        }

                    });
                   
                }
            })
});
//batas hapus admin
! function(window, document, $) {
        "use strict";
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation();
    }(window, document, jQuery).on('show.bs.modal', function(event) {
    // prevent datepicker from firing bootstrap modal "show.bs.modal"
    event.stopPropagation();
});
</script>