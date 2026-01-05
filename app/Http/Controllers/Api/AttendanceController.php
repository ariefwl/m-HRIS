<?php
namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\attendance;
use Illuminate\Http\Request;
use App\Models\OfficeLocations;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Clock\now;

class AttendanceController extends Controller
{
    // get office location
    public function office(): JsonResponse
    {
        $office = OfficeLocations::where('is_active', 1)->first();
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

    public function checkin(StoreAttendanceRequest $request): JsonResponse
    {
        $office = OfficeLocations::where('is_active', 1)->first();
        
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

        // validasi check_type
        if (! in_array($request->check_type, ['IN', 'OUT'])) {
            return response()->json([
                'status' => false,
                'message' => 'Tipe absensi tidak valid'
            ], 422);
        }

        // cek akurasi GPS
        $maxAccuracy = 100;

        if ($request->accuracy && $request->accuracy > $maxAccuracy) {
            return response()->json([
                'status' => false,
                'message' => 'Akurasi GPS terlalu rendah',
                'accuracy' => $request->accuracy
            ], 422);
        }

        // cek perangkat 
        $device = strtolower($request->header('User-Agent'));

        if (str_contains($device, 'emulator') || str_contains($device, 'android sdk built for')) {
            return response()->json([
                'status' => false,
                'message' => 'Absensi dari emulator tidak diizinkan'
            ], 403);
        }

        return DB::transaction(function () use ($request, $distance) {

            $today = today();

            $todayAttendances = Attendance::where('user_id', auth()->id())
                ->whereDate('check_time', $today)
                ->lockForUpdate()
                ->get();

            $alreadyIn  = $todayAttendances->contains('check_type', 'IN');
            $alreadyOut = $todayAttendances->contains('check_type', 'OUT');

            if ($request->check_type === 'IN' && $alreadyIn) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Anda sudah melakukan absensi masuk hari ini'
                ], 409);
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
            }

            // simpan foto selfie
            // $photoPath = null;
            // if ($request->hasFile('photo')) {
            //     $photoPath = $request->file('photo')
            //         ->store('attendance', 'public');
            // }

            Attendance::create([
                'user_id'               => auth()->id(),
                'nik'                   => auth()->user()->nik,
                'check_type'            => $request->check_type,
                'check_time'            => now(),
                'latitude'              => $request->latitude,
                'longitude'             => $request->longitude,
                'accuracy'              => $request->accuracy,
                'distance_from_office'  => round($distance),
                'device_info'           => $request->header('User-Agent'),
                // 'photo_path'            => $photoPath,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Absensi berhasil'
            ]);
        });
    }

    public function today(Request $request)
    {
        // logger('NOW APP', [Carbon::now()->toDateTimeString()]);
        // logger('NOW DB', [DB::select('select GETDATE() as db_time')[0]->db_time]);
        $start = Carbon::today('Asia/Jakarta')->utc();
        $end   = Carbon::tomorrow('Asia/Jakarta')->utc();

        $attendance = Attendance::where('user_id', auth()->id())
            ->whereBetween('check_time', [$start, $end])
            ->get();
            
        return response()->json([
            'check_in'  => $attendance->contains('check_type', 'IN'),
            'check_out' => $attendance->contains('check_type', 'OUT'),
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
