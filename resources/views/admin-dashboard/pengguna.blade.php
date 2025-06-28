@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0">
        <h3 class="fw-bold fs-4 mb-3">Daftar Pengguna</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <button class="btn btn-sm btn-primary" onclick="penggunaModal()"><i class='bx bx-plus'></i> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="penggunaTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Nama Pengguna</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status Akun</th>
                            <th class="th-aksi">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="penggunaModal" tabindex="-1" aria-labelledby="penggunaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="penggunaModalLabel">Tambah data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" id="penggunaForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_id" id="user_id">
                        <div class="form-group mb-3">
                            <label for="nama_user" class="form-label">Nama Pengguna</label>
                            <input type="text" class="form-control" id="nama_user" placeholder="Masukkan Nama Pengguna"
                                name="nama_user" value="{{ old('nama_user') }}">
                            <div class="invalid-feedback" id="error-nama_user"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" placeholder="Masukkan Username"
                                name="username" value="{{ old('username') }}">
                            <div class="invalid-feedback" id="error-username"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Masukkan Email"
                                name="email" value="{{ old('email') }}">
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>
                        <!-- Password -->
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Masukkan Password">
                                <span class="input-group-text toggle-password" data-target="password"
                                    style="cursor: pointer;">
                                    <i class='bx bx-show'></i>
                                </span>
                            </div>
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="form-group mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Masukkan Ulang Passowrd">
                                <span class="input-group-text toggle-password" data-target="password_confirmation"
                                    style="cursor: pointer;">
                                    <i class='bx bx-show'></i>
                                </span>
                            </div>
                            <div class="invalid-feedback" id="error-password_confirmation"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-control select-role">
                                <option value="" selected disabled>Pilih Role</option>
                            </select>
                            <div class="invalid-feedback" id="error-role"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" id="saveBtn" class="btn btn-primary btn-sm">Simpan</button>
                        </div>
                    
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let method;
        let user_id = null;

        $(document).ready(function() {
            document.querySelectorAll(".toggle-password").forEach(toggle => {
                toggle.addEventListener("click", function() {
                    const targetId = this.getAttribute("data-target");
                    const passwordField = document.getElementById(targetId);
                    const icon = this.querySelector("i");

                    if (passwordField.type === "password") {
                        passwordField.type = "text";
                        icon.classList.remove("bx-show");
                        icon.classList.add("bx-low-vision");
                    } else {
                        passwordField.type = "password";
                        icon.classList.remove("bx-low-vision");
                        icon.classList.add("bx-show");
                    }
                });
            });

            penggunaTable();
            showSelect();
        });

        function penggunaTable() {
            var table = $('#penggunaTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('pengguna.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'nama_user',
                        name: 'nama_user'
                    },
                    {
                        data: 'username',
                        name: 'username',
                    },
                    {
                        data: 'email',
                        name: 'email',
                    },
                    {
                        data: 'role',
                        name: 'role',
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        }

        function penggunaModal() {
            $('#penggunaForm')[0].reset();
            method = 'create';
            user_id;

            $('#penggunaModal').modal('show');
            $('#penggunaModalLabel').text('Tambah Data Pengguna');
            $('#saveBtn').text('Simpan');
        }

        function showSelect() {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('pengguna.create') }}',
                type: 'GET',
                success: function(response) {
                    if (response.status == 200) {
                        response.role.forEach(function(role) {
                            $('#role').append(
                                `<option value="${role}">${role}</option>`
                            );
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 500) {
                        let errorResponse = xhr.responseJSON;
                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.title || "Error",
                            text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                        });
                    }
                }
            });
        }


        $('#penggunaForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('pengguna.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!user_id) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Pengguna ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('pengguna.update', '') }}/' + user_id;
                formData.append('_method', 'PUT'); // Laravel expects PUT for updates
                httpMethod = 'POST'; // FormData does not support PUT, so use POST with `_method`
            }

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: httpMethod,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status == 200) {
                        $('#penggunaModal').modal('hide');
                        $('#penggunaForm').trigger('reset');

                        $('#penggunaTable').DataTable().ajax.reload();

                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);

                            // Tambahkan class is-invalid dan tampilkan pesan error
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }

                            // Tangani kasus konfirmasi seperti 'password_confirmation'
                            if (key.includes('password') && key !== 'password_confirmation') {
                                // Jika key 'password', tangani juga konfirmasi
                                $('#password_confirmation').addClass('is-invalid');
                                $('#error-password_confirmation').text(value[0]);
                            }
                        });

                        $('input, select, textarea').on('input change', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });

                    } else {
                        let errorResponse = xhr.responseJSON; // Ambil data JSON error

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.title || "Error",
                            text: errorResponse.message ||
                                "Terjadi kesalahan yang tidak diketahui.",
                        });
                    }
                }
            });
        });

        function editUser(e) {
            user_id = e.getAttribute('data-id');
            method = 'update';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('pengguna.show', '') }}/" + user_id,
                type: "GET",
                success: function(response) {
                    $('#user_id').val(response.user.user_uuid);
                    $('#nama_user').val(response.user.nama_user);
                    $('#username').val(response.user.username);
                    $('#email').val(response.user.email);
                    $('#password').val(''); // kosongkan agar user isi sendiri jika ingin ganti
                    $('#password_confirmation').val('');
                    $('#role').val(response.user.role);
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // 422 = Error Validasi Laravel
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }
                        });

                        $('.form-control').on('input', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });

                    } else {
                        let errorResponse = xhr.responseJSON; // Ambil data JSON error

                        Swal.fire({
                            icon: errorResponse.icon || "error",
                            title: errorResponse.title || "Error",
                            text: errorResponse.message || "Terjadi kesalahan yang tidak diketahui.",
                        });
                    }
                }
            });

            $('#penggunaModal').modal('show');
            $('#penggunaModalLabel').text('Edit Data Pengguna');
            $('#saveBtn').text('Ubah');
        }

        function deleteUser(e) {
            let user_id = e.getAttribute('data-id');

            Swal.fire({
                title: "Apakah anda yakin?",
                text: "Menghapus data secara permanen",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Tidak",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('pengguna.destroy', '') }}/" + user_id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}", // Kirim token dalam body
                        },
                        success: function(response) {
                            if (response.status == 200) {
                                $('#penggunaTable').DataTable().ajax.reload();
                                Swal.fire({
                                    icon: response.icon,
                                    title: response.title,
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorResponse = xhr.responseJSON; // Ambil data JSON error

                            Swal.fire({
                                icon: errorResponse.icon || "error",
                                title: errorResponse.title || "Error",
                                text: errorResponse.message ||
                                    "Terjadi kesalahan yang tidak diketahui.",
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
