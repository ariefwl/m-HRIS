<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function create()
    {
        $currDate = date('Y-m-d');
        $nik = Auth::user()->nik;
        $cek = Presensi::where('tgl_presensi', $currDate)->where('nik', $nik)->count();
        return view('presensi.create', compact('cek'));
    }

    public function store(Request $request)
    {
        $nik = Auth::user()->nik;
        $lokasi = $request->lokasi;
        $image = $request->image;
        $tgl_presensi = date('Y-m-d');
        $jam = date('H:i:s');
        $lat_kantor = -7.1057531;
        $long_kantor = 110.4619591;
        $lokasi = $request->lokasi;
        // dd($lokasi);
        $lok_user = explode(',', $lokasi);
        $lat_user = $lok_user[0];
        $long_user = $lok_user[1];

        $jarak = $this->distance($lat_kantor, $long_kantor, $lat_user, $long_user);
        $radius = round($jarak['meters']);
        // dd($radius);
        $image = $request->image;
        $folderPath = "public/uploads/absensi";
        $formatName = $nik . "-" . $tgl_presensi;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderPath . $fileName;

        $cek = Presensi::where('tgl_presensi', $tgl_presensi)->where('nik', $nik)->count();

        if ($radius > 10) {
            echo "error|Maaf anda di luar radius anda berada " .$radius. " meter dari kantor !";
        } else {
            if ($cek > 0) {
                $data_out = [
                    'jam_out' => $jam,
                    'foto_out' => $fileName,
                    'lokasi_out' => $lokasi
                ];
                $update = Presensi::where('tgl_presensi', $tgl_presensi)->where('nik', $nik)->update($data_out);
    
                if ($update) {
                    echo "success|Hati - hati di jalan !|out";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Maaf gagal Absen pulang, Hubungi HRD !|out";
                }
            } else {
                $data_in = [
                    'nik' => $nik,
                    'tgl_presensi' => $tgl_presensi,
                    'jam_in' => $jam,
                    'foto_in' => $fileName,
                    'lokasi_in' => $lokasi
                ];
                $simpan = Presensi::insert($data_in);
                if ($simpan) {
                    echo "success|Selamat bekerja !|in";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Maaf gagal Absen, Hubungi HRD !|in";
                }
            }
        }
    }

     //Menghitung Jarak
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }
}
