@extends('auth-page.layout.main')

@section('child-auth')
    <div class="header-text mb-3">
        <p class="fs-3 text-center">Lupa Password</p>
    </div>
    <p>Masukkan email anda dan kita akan mengirimkan link untuk reset password.</p>
    <form action="#" id="resetEmailForm">
        @csrf
        <div class="form-group mb-5">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control" id="email" placeholder="Masukkan Email" name="email"
                value="{{ old('email') }}" required>
            <div class="invalid-feedback" id="error-email"></div>
        </div>
        <div class="form-group mb-3">
            <button type="submit" class="btn btn-primary w-100 fs-6" id="btn-login">Reset Password</button>
        </div>
        <a href="{{ route('auth.form.reset') }}">Reset</a>
        <a href="{{ route('auth.login') }}" class="btn btn-outline-secondary w-100 fs-6">Kembali ke halaman login</a>
    </form>
@endsection

@section('script')
    <script>
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btn-login');

            // Add loading spinner inside the button
            btn.html(
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
                    $('#btn-login').text('Masuk');

                    if (xhr.status == 422) { // 422 = Error Validasi Laravel
                        let errors = xhr.responseJSON.errors; // Pastikan objek errors ada

                        // Loop semua error dan tampilkan di input yang sesuai
                        $.each(errors, function(key, value) {
                            let inputField = $('[name="' + key + '"]');
                            let errorField = $('#error-' + key);
                            inputField.addClass('is-invalid');
                            if (errorField.length) {
                                errorField.text(value[0]); // Ambil error pertama
                            }
                        });

                        // Hapus error saat pengguna mengetik
                        $('.form-control').on('input', function() {
                            $(this).removeClass('is-invalid');
                            $('#error-' + $(this).attr('name')).text('');
                        });

                    } else if (xhr.status == 401) {
                        let errorResponse = xhr.responseJSON

                        Swal.fire({
                            icon: errorResponse.icon,
                            title: errorResponse.title,
                            text: errorResponse.message,
                        });
                    }
                }
            });
        });
    </script>
@endsection
