<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $currDate = date('Y-m-d');
        $currMonth = date('m') * 1;
        $currYear = date('Y');
        $start = now()->startOfMonth()->toDateString();
        $end   = now()->endOfMonth()->toDateString();
        $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        // dd($namaBulan[$currMonth]);
        $nik = Auth::user()->nik;
        $presensiHariIni = Presensi::where('tgl_presensi', $currDate)->where('nik', $nik)->first();
        $historyBulanIni = Presensi::whereBetween('tgl_presensi', [$start, $end])
                 ->where('nik', $nik)
                 ->OrderBy('tgl_presensi')
                 ->get();
        
        return view('dashboard.dashboard', compact('presensiHariIni','historyBulanIni', 'namaBulan', 'currMonth', 'currYear'));
    }
}
