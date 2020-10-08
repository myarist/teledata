<script>
    $(".statusonline").click(function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var flag = $(this).data('flag');
    if (flag == 1)
    {
        var flagtext = 'OFFLINE';
    }
    else
    {
        var flagtext = 'ONLINE';
    }
    
    Swal.fire({
                title: 'Edit Status?',
                text: "Status Online akan diset ke "+flagtext,
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
                        url : '{{route('admin.statusonline')}}',
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

</script>