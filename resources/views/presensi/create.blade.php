@extends('layouts.presensi')

@section('header')
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Absensi</div>
        <div class="right"></div>
    </div>

    <style>
        .webcam-capture,
        .webcam-capture video {
            display: inline-block;
            width: 100% !important;
            margin: auto;
            height: auto !important;
            border-radius: 15%;
        }
        #map {
            height: 200px;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('content')
    <div class="row" style="margin-top: 70px;">
        <div class="col">
            <input type="hidden" id="lokasi">
           <div class="webcam-capture"></div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            @if ($cek > 0)
                <button id="absen" class="btn btn-danger btn-block">Absen Pulang</button>
            @else
                <button id="absen" class="btn btn-primary btn-block">Absen Masuk</button>
            @endif
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>
@endsection

@push('script')
<script>
    Webcam.set({
        height: 480,
        width: 640,
        image_format: 'jpeg',
        jpeg_quality: 80
    });

    Webcam.attach('.webcam-capture');

    var lokasi = document.getElementById('lokasi');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
    }

    function successCallback(position){
        lokasi.value = position.coords.latitude + "," + position.coords.longitude;

        var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 18);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);

        var circle = L.circle([-7.1057531, 110.4619591], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: 50
        }).addTo(map);
    }

    function errorCallback(){

    }

    $('#absen').click(function(){
        Webcam.snap(function(uri){
            image = uri;
        })
        var lokasi = $('#lokasi').val();
        
        $.ajax({
            type: 'POST',
            url: 'store',
            data: {
                _token: '{{ csrf_token() }}',
                image: image,
                lokasi: lokasi
            },
            cache:false,
            success:function(res){
                var status = res.split("|");
                if (status[0] == 'success') {
                    Swal.fire({
                        title: "Berhasil Absen !",
                        text: status[1],
                        icon: "success"
                    })
                    setTimeout("location.href='/dashboard'", 3000);
                } else {
                    Swal.fire({
                        title: "Gagal absen ! !",
                        text: status[1],
                        icon: "success"
                    })
                    setTimeout("location.href='/dashboard'", 3000);
                }
            }
        })
    })
</script>
@endpush