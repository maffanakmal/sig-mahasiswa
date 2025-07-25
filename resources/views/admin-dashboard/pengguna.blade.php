@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="mb-0">
        <h3 class="fw-bold fs-4 mb-3">Daftar Pengguna</h3>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-end align-items-center">
            <button id="tambahPenggunaBtn" class="btn btn-primary btn-sm" onclick="penggunaModal()"><box-icon name='plus' color='white' class="icon-crud"></box-icon> Tambah</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="penggunaTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th-number">No</th>
                            <th>Nama Lengkap</th>
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
                        <input type="hidden" name="user_uuid" id="user_uuid">
                        <div class="form-group mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" placeholder="Masukkan Nama Lengkap"
                                name="nama_lengkap" value="{{ old('nama_lengkap') }}" maxlength="100" required>
                            <div class="invalid-feedback" id="error-nama_lengkap"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" placeholder="Masukkan Username"
                                name="username" value="{{ old('username') }}" maxlength="50" required>
                            <div class="invalid-feedback" id="error-username"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Masukkan Email"
                                name="email" value="{{ old('email') }}" maxlength="50">
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>
                        <!-- Password -->
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Masukkan Password" minlength="5" maxlength="60">
                                <span class="input-group-text toggle-password" data-target="password"
                                    style="cursor: pointer;">
                                    <box-icon name='low-vision'></box-icon>
                                </span>
                            </div>
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="form-group mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password"
                                    name="confirm_password" placeholder="Masukkan Ulang Password" minlength="5"
                                    maxlength="60">
                                <span class="input-group-text toggle-password" data-target="confirm_password"
                                    style="cursor: pointer;">
                                    <box-icon name='low-vision'></box-icon>
                                </span>
                            </div>
                            <div class="invalid-feedback" id="error-confirm_password"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-control select-role" required>
                                <option value="" selected disabled>Pilih Role</option>
                                <option value="BAAKPSI">BAAKPSI</option>
                                <option value="Warek 3">Warek 3</option>
                                <option value="PMB">PMB</option>
                            </select>
                            <div class="invalid-feedback" id="error-role"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm"
                                data-bs-dismiss="modal">Batal</button>
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
        let user_uuid = null;

        $(document).ready(function () {
            document.querySelectorAll(".toggle-password").forEach(toggle => {
                toggle.addEventListener("click", function () {
                    const targetId = this.getAttribute("data-target");
                    const passwordField = document.getElementById(targetId);
                    const boxIcon = this.querySelector("box-icon");

                    if (passwordField.type === "password") {
                        passwordField.type = "text";
                        boxIcon.setAttribute("name", "show-alt");
                    } else {
                        passwordField.type = "password";
                        boxIcon.setAttribute("name", "low-vision");
                    }
                });
            });
            penggunaTable();
        });

        function penggunaTable() {
            var table = $('#penggunaTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('pengguna.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                    },
                    {
                        data: 'nama_lengkap',
                        name: 'nama_lengkap'
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
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            method = 'create';
            user_uuid;

            $('#penggunaModal').modal('show');
            $('#penggunaModalLabel').text('Tambah Data Pengguna');
            $('#saveBtn').text('Simpan');
        }

        $('#penggunaForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#saveBtn');

            btn.prop('disabled', false).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('pengguna.store') }}';
            let httpMethod = 'POST'; // Default method for create

            if (method === 'update') {
                if (!user_uuid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan!',
                        text: 'Pengguna ID tidak ditemukan.',
                    });
                    return;
                }

                url = '{{ route('pengguna.update', '') }}/' + user_uuid;
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
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

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
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

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

                    } else if (xhr.status === 400) {
                        Swal.fire({
                            icon: xhr.responseJSON.icon,
                            title: xhr.responseJSON.title,
                            text: xhr.responseJSON.message
                        });
                        return;
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
            user_uuid = e.getAttribute('data-id');
            method = 'update';

            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            let btn = $('#saveBtn');
            
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('pengguna.show', '') }}/" + user_uuid,
                type: "GET",
                success: function(response) {
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

                    $('#user_uuid').val(response.user.user_uuid);
                    $('#nama_lengkap').val(response.user.nama_lengkap);
                    $('#username').val(response.user.username);
                    $('#email').val(response.user.email);
                    $('#password').val('');
                    $('#password_confirmation').val('');
                    $('#role').val(response.user.role);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(method === 'update' ? 'Ubah' : 'Simpan');

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

                        $('input, select, textarea').on('input change', function() {
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
            let user_uuid = e.getAttribute('data-id');

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
                        url: "{{ route('pengguna.destroy', '') }}/" + user_uuid,
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
