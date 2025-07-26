@extends('auth-page.layout.main')

@section('child-auth')
    <div class="header-text mb-3">
        <p class="fs-3 text-center">Validasi Email</p>
    </div>
    <p>Kami akan kirimkan link reset password ke email Anda.</p>
    <form action="#" id="resetForm">
        @csrf
        <div class="form-group mb-5">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control" id="email" placeholder="Masukkan Email" name="email"
                value="{{ old('email') }}" required>
            <div class="invalid-feedback" id="error-email"></div>
        </div>
        <div class="form-group mb-3">
            <button type="submit" class="btn btn-primary w-100 fs-6" id="btn-reset">Validasi</button>
        </div>
        <a href="{{ route('auth.login') }}" class="btn btn-outline-secondary w-100 fs-6">Kembali ke halaman login</a>
    </form>
@endsection

@section('script')
    <script>
        $('#resetForm').on('submit', function(e) {
            e.preventDefault();

            let btn = $('#btn-reset');
            
            btn.prop('disabled', true).html(
                '<div class="spinner-border spinner-border-sm text-light mb-0" role="status"><span class="visually-hidden">Loading...</span></div>'
            );

            const formData = new FormData(this);
            let url = '{{ route('auth.email') }}';

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
                    btn.prop('disabled', false).html('Reset Password');

                    if (response.status === 200) {
                        Swal.fire({
                            icon: response.icon,
                            title: response.title,
                            text: response.message,
                        }).then(() => {
                            window.location.href = "{{ route('auth.login') }}";
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('Reset Password');

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

                    } else if (xhr.status == 401 || xhr.status == 404) {
                        let errorResponse = xhr.responseJSON;

                        Swal.fire({
                            icon: errorResponse.icon,
                            title: errorResponse.title,
                            text: errorResponse.message,
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Silakan coba lagi nanti.'
                        });
                    }
                }
            });
        });
    </script>
@endsection
