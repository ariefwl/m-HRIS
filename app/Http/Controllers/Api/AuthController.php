<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('nik','password'))) {
            // return response()->json([
            //     'message' => 'NIK atau password salah !'
            // ], 401);
            return ApiResponse::error('Nik atau password salah !', 401);
        }

        // $user = Auth::user();
        // HAPUS token lama (optional)
        // $user->tokens()->delete();

        $user = $request->user();

        // BUAT token baru
        $token = $user->createToken('mobile-token',['attendance'])->plainTextToken;

        return ApiResponse::success(
            'Login berhasil !',
            [
                'token' => $token,
                'user' => $user,   
            ]
        );
        // return response()->json([
        //     'message' => 'Login berhasil',
        //     'data' => [
        //         'token' => $token,
        //         'user' => [
        //             'id' => $user->id,
        //             'nik' => $user->nik,
        //             'name' => $user->name,
        //             'email' => $user->email
        //         ]
        //     ]
        // ], 200);
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Data user berhasil di hapus',
                'data' => null
            ],200);
        } catch(Exception $error) {
            return response()->json([
                'message' => 'Terjadi kesalahan !',
                'error' => $error->getMessage()
            ],500);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
