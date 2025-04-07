@extends('landing-page.layout.main')

@section('jumbotron')
<div class="container col-xxl-8 px-4 py-5">
    <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
        <div class="col-10 col-sm-8 col-lg-6 mx-auto text-center">
            <img src="{{ asset('img/pesanggrahan.png') }}" id="peta-pesanggrahan" class="d-block mx-auto img-fluid" alt="peta-pesanggrahan">
        </div>
        <div class="col-lg-6 text-center text-lg-start">
            <h2 class="fw-bold lh-1 mb-3">Selamat Datang di Gisapp</h2>
        </div>
    </div>
</div>
@endsection