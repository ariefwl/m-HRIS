<?php
namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\attendance;
use Illuminate\Http\Request;
use App\Models\office_locations;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

use function Symfony\Component\Clock\now;

class AttendanceController extends Controller
{
    // get office location
    public function office(): JsonResponse
    {
        $office = office_locations::where('is_active', 1)->first();
        if (!$office) {
            return response()->json([
                'status' => false,
                'message' => 'Lokasi kantor belum di konfigurasi !'
            ], 404); 
        } else {
            return response()->json([
                'status' => true,
                'data' => $office
            ]);
        }

    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $office = office_locations::where('is_active', 1)->first();
        
        if (!$office) {
            return response()->json([
                'status' => false,
                'message' => 'Lokasi kantor belum di konfigurasi !'
            ], 422); 
        }

        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $office->latitude,
            $office->longitude
        );

        if ($distance > $office->radius_meters) {
            return response()->json([
                'status' => false,
                'message' => 'Di luar area kantor',
                'distance' => round($distance)
            ], 403);
        }

        // validasi absen hari berjalan
        $today = now()->toDateString();
        $todayAttendances = attendance::where('user_id', auth()->id())
            ->whereDate('check_time', $today)
            ->get();

        $alreadyIn  = $todayAttendances->contains('check_type', 'IN');
        $alreadyOut = $todayAttendances->contains('check_type', 'OUT');

        if ($request->check_type === 'IN') {
            if ($alreadyIn) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Anda sudah melakukan absensi masuk hari ini'
                ], 409);
            }
        }

        if ($request->check_type === 'OUT') {
            if (! $alreadyIn) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Anda belum melakukan absensi masuk'
                ], 403);
            }

            if ($alreadyOut) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Anda sudah melakukan absensi pulang hari ini'
                ], 409);
            }

            $lastIn = $todayAttendances
                ->where('check_type', 'IN')
                ->sortByDesc('check_time')
                ->first();

            if ($lastIn && now()->lt($lastIn->check_time)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Waktu absensi tidak valid'
                ], 422);
            }
        }

        // cek akurasi GPS
        $maxAccuracy = 30;

        if ($request->accuracy && $request->accuracy > $maxAccuracy) {
            return response()->json([
                'status' => false,
                'message' => 'Akurasi GPS terlalu rendah',
                'accuracy' => $request->accuracy
            ], 422);
        }

        // cek perangkat 
        $device = strtolower($request->header('User-Agent'));

        if (str_contains($device, 'emulator') || str_contains($device, 'sdk')) {
            return response()->json([
                'status' => false,
                'message' => 'Absensi dari emulator tidak diizinkan'
            ], 403);
        }

        attendance::create([
            'user_id' => auth()->id(),
            'nik' => auth()->user()->nik,
            'check_type' => $request->check_type,
            'check_time' => Carbon::now(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'distance_from_office' => round($distance),
            'device_info' => $request->header('User-Agent')
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Absensi berhasil'
        ]);
    }

    public function history(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date'
        ]);

        $date = $request->date ?? now()->toDateString();

        $data = attendance::where('nik', auth()->user()->nik)
            ->whereDate('check_time', $date)
            ->orderBy('check_time')
            ->get();

        return ApiResponse::success([
            'Riwayat absensi',
            [
                'date' => $date,
                'items' => $data
            ]
        ]);
    }

    public function dailySummary(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $data = attendance::where('nik', auth()->id())
                       ->whereDate('check_time', $date)
                       ->selectRaw("
                            MIN(CASE WHEN check_type = 'IN' THEN check_time END)  AS time_in,
                            MAX(CASE WHEN check_type = 'OUT' THEN check_time END) AS time_out
                       ")->first();
        
        return ApiResponse::success(
            'Rekap absensi harian',
            [
                'date' => $date,
                'time_in' => $data->time_in,
                'time_out' => $data->time_out,
            ] 
        );
    }

    // ===============================
    // 4. HAVERSINE FUNCTION
    // ===============================
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
