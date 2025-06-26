@extends('auth-page.layout.main')

@section('child-auth')
    <div class="header-text mb-3">
        <p class="fs-3 text-center">Pengaturan Akun</p>
    </div>
    <form action="#" id="pengaturanAkunForm">
        @csrf
        <div class="form-group mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control" id="email" placeholder="Masukkan Email" name="email"
                value="{{ old('email') }}" required>
            <div class="invalid-feedback" id="error-email"></div>
        </div>
        <!-- Password -->
        <div class="form-group mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password">
                <span class="input-group-text toggle-password" data-target="password" style="cursor: pointer;">
                    <i class='bx bx-show'></i>
                </span>
            </div>
            <div class="invalid-feedback" id="error-password"></div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group mb-4">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    placeholder="Masukkan Ulang Passowrd">
                <span class="input-group-text toggle-password" data-target="password_confirmation" style="cursor: pointer;">
                    <i class='bx bx-show'></i>
                </span>
            </div>
            <div class="invalid-feedback" id="error-password_confirmation"></div>
        </div>
        <div class="form-group mb-3">
            <button type="submit" class="btn btn-primary w-100 fs-6" id="btn-login">Ubah Password</button>
        </div>
        <a href="{{ route('auth.reset.password') }}">Kembali</a>
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
