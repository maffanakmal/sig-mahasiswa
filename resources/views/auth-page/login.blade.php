@extends('auth-page.layout.main')

@section('child-auth')
    <div class="header-text mb-4">
        <p class="fs-3 text-center">Masuk ke Akun</p>
    </div>
    @if (session('session_expired'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Sesi habis:</strong> {{ session('session_expired') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <form action="#" id="loginForm">
        @csrf
        <div class="form-group mb-2">
            <label for="credentials" class="form-label">Username/Email</label>
            <input type="text" class="form-control" id="credentials" placeholder="Masukkan Username atau Email"
                name="credentials" value="{{ old('credentials') }}" maxlength="50" required>
            <div class="invalid-feedback" id="error-credentials"></div>
        </div>
        <div class="form-group mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password"
                    maxlength="60" required>
                <span class="input-group-text toggle-password" data-target="password" style="cursor: pointer;">
                    <i class='bx bx-show'></i>
                </span>
            </div>
            <div class="invalid-feedback" id="error-password"></div>
        </div>
        <div class="form-group mb-3">
            <button type="submit" class="btn btn-primary w-100 fs-6" id="btn-login">Masuk</button>
        </div>
        <div class="form-group mb-3">
            <p>Lupa password? <a href="{{ route('auth.validate.email') }}">Reset Password</a></p>
        </div>
        <a href="{{ route('landing.index') }}" class="btn btn-outline-secondary w-100 fs-6">Kembali</a>
    </form>
@endsection

@section('script')
    <script>
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
        })


        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btn-login');

            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('login.check') }}';

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
                    btn.prop('disabled', false).html('Masuk');

                    if (response.status === 200) {
                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = "{{ route('home.index') }}";
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('Masuk');

                    if (xhr.status == 422) {
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

                    } else if (xhr.status == 401 || xhr.status == 403) {
                        let errorResponse = xhr.responseJSON;

                        Swal.fire({
                            icon: errorResponse.icon || 'error',
                            title: errorResponse.title || 'Login Gagal',
                            text: errorResponse.message || 'Terjadi kesalahan otentikasi.',
                        });

                    } else if (xhr.status == 404) {
                        let errorResponse = xhr.responseJSON;

                        Swal.fire({
                            icon: errorResponse.icon || 'error',
                            title: errorResponse.title || 'Akun Tidak Ditemukan',
                            text: errorResponse.message ||
                                'Username atau email tidak ditemukan.',
                        });

                    } else if (xhr.status == 500) {
                        let errorResponse = xhr.responseJSON;

                        Swal.fire({
                            icon: errorResponse.icon || 'error',
                            title: errorResponse.title || 'Kesalahan Server',
                            text: errorResponse.message || 'Terjadi kesalahan di server.',
                        });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Tidak Dikenal',
                            text: 'Terjadi kesalahan yang tidak diketahui.',
                        });
                    }
                }
            });
        });
    </script>
@endsection
