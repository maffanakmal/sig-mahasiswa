@extends('auth-page.layout.main')

@section('child-auth')
    <div class="header-text mb-3">
        <p class="fs-3 text-center">Reset Password</p>
    </div>
    <form action="#" id="resetPasswordForm">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="reset_token" value="{{ $reset_token }}">
        <div class="form-group mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control" id="email" placeholder="Masukkan Email" name="email"
                value="{{ $email }}" disabled>
            <div class="invalid-feedback" id="error-email"></div>
        </div>
        <!-- Password -->
        <div class="form-group mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password Baru" maxlength="60" required>
                <span class="input-group-text toggle-password" data-target="password" style="cursor: pointer;">
                    <box-icon name='low-vision'></box-icon>
                </span>
            </div>
            <div class="invalid-feedback" id="error-password"></div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group mb-4">
            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                    placeholder="Masukkan Ulang Password Baru" maxlength="60" required>
                <span class="input-group-text toggle-password" data-target="confirm_password" style="cursor: pointer;">
                    <box-icon name='low-vision'></box-icon>
                </span>
            </div>
            <div class="invalid-feedback" id="error-confirm_password"></div>
        </div>
        <div class="form-group mb-3">
            <button type="submit" class="btn btn-primary w-100 fs-6" id="btn-reset">Ubah Password</button>
        </div>
    </form>
@endsection

@section('script')
    <script>
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
        });

        $('#resetPasswordForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btn-reset');
            
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            const url = '{{ route('auth.update.password') }}';

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
                        btn.prop('disabled', false).html('Ubah Password');

                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = "/login";
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('Ubah Password');
                    
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
