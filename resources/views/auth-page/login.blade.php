@extends('auth-page.layout.main')

@section('child-auth')
    <div class="header-text mb-4">
        <p class="fs-3 text-center">Selamat datang</p>
    </div>
    <form action="" method="POST" id="login-form">
        @csrf
        <div class="form-group mb-2">
            <label for="nip" class="form-label">NIP</label>
            <input type="text" class="form-control" id="nip" placeholder="NIP" name="nip"
                value="{{ old('nip') }}" autofocus required>
            <div class="invalid-feedback" id="error-nip"></div>
        </div>
        <div class="form-group mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                    <i class='bx bx-show' id="toggleIcon"></i>
                </span>
            </div>
            <div class="invalid-feedback" id="error-password"></div>
        </div>
        <div class="form-group mb-2">
            <button type="submit" class="btn btn-primary w-100 fs-6" id="btn-login">Masuk</button>
        </div>
    </form>
    <small class="text-center mb-2">Tidak Punya Akun?</small>
    <div class="input-group mb-2">
        <a href="" class="btn btn-outline-secondary w-100 fs-6">Daftar</a>
    </div>
@endsection

@section('script')
    {{-- <script>
        $(function() {
            $('#login-form').on('submit', function(e) {
                e.preventDefault();

                let btn = $('#btn-login');

                // Add loading spinner inside the button
                btn.html(
                    '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
                    );

                const formData = new FormData(this);
                let url = '{{ route('login.auth') }}';

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
                                window.location.href = "{{ route('dashboard') }}";
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
        });
    </script> --}}
@endsection
