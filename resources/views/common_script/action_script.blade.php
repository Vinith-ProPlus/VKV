<script>

    function commonRestore(element) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to restore it!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, restore it!',
            color:'black',
            background:'white',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "PUT",
                    url: element,
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $('#list_table').DataTable().ajax.reload();
                        Swal.fire({
                            position: 'center',
                            icon: response.status,
                            title: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                            color:'black',
                            background:'white',
                        })
                    },
                    error: function (error) {
                        $('#list_table').DataTable().ajax.reload();
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: error.message,
                            showConfirmButton: false,
                            timer: 2000,
                            color:'black',
                            background:'white',
                        })
                    }
                });
            }
        })
    }
    function commonDelete(element) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete it!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            color:'black',
            background:'white',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: element,
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $('#list_table').DataTable().ajax.reload();
                        Swal.fire({
                            position: 'center',
                            icon: response.status,
                            title: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                            color:'black',
                            background:'white',
                        })
                    },
                    error: function (error) {
                        $('#list_table').DataTable().ajax.reload();
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: error.message,
                            showConfirmButton: false,
                            timer: 2000,
                            color:'black',
                            background:'white',
                        })
                    }
                });
            }
        })
    }

    function commonApprove(element) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to approve it!",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Approve it!',
            color:'black',
            background:'white',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: element,
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        $('#list_table').DataTable().ajax.reload();
                        Swal.fire({
                            position: 'center',
                            icon: response.status,
                            title: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                            color:'black',
                            background:'white',
                        })
                    },
                    error: function (error) {
                        $('#list_table').DataTable().ajax.reload();
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: error.message,
                            showConfirmButton: false,
                            timer: 2000,
                            color:'black',
                            background:'white',
                        })
                    }
                });
            }
        })
    }
</script>
