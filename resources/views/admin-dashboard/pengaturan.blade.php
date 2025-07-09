@extends('admin-dashboard.layout.main')

@section('child-content')
    <div class="card w-50 mx-auto">
        <div class="card-header">
            <p class="fs-4 text-center">Pengaturan Akun</p>
        </div>
        <div class="card-body">
            <form action="#" id="pengaturanAkunForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_uuid" id="user_uuid" value="{{ $user->user_uuid }}">
                <div class="form-group mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" placeholder="Masukkan Nama Lengkap"
                        name="nama_lengkap" value="{{ $user->nama_lengkap }}">
                    <div class="invalid-feedback" id="error-nama_lengkap"></div>
                </div>
                <div class="form-group mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" placeholder="Masukkan Username"
                        name="username" value="{{ $user->username }}">
                    <div class="invalid-feedback" id="error-username"></div>
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Masukkan Email" name="email"
                        value="{{ $user->email }}">
                    <div class="invalid-feedback" id="error-email"></div>
                </div>
                <div class="form-group mb-4">
                    <label for="role" class="form-label">Role</label>
                    <input type="role" class="form-control" id="role" name="role" placeholder="Role"
                        value="{{ $user->role }}" disabled>
                    <div class="invalid-feedback" id="error-role"></div>
                </div>
                <div class="saveBtn d-flex justify-content-between">
                    <a href="{{ route('home.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    <button type="submit" id="saveBtn" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $('#pengaturanAkunForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            let url = '{{ route('home.pengaturan.store') }}';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status == 200) {
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

                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]);
                            }
                        });

                        $('input, select, textarea').on('input change', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });

                    } else if (xhr.status === 400) {
                        let response = xhr.responseJSON;
                        Swal.fire({
                            icon: response.icon || 'info',
                            title: response.title || 'Tidak Ada Perubahan',
                            text: response.message || 'Tidak ada perubahan pada data.',
                        });

                    } else {
                        let errorResponse = xhr.responseJSON;
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
    </script>
@endsection
