<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // $pass = 'password';
        // dd(Hash::make($pass));
        if (Auth::attempt([
            'nik' => $request->nik,
            'password' => $request->password
        ])) {
            // return response('Login Berhasil', 200);
            return redirect('/dashboard');
        } else {
            return redirect('/')->with('message', 'NIK / Password Salah !');
        }
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
            return redirect('/');
        }
    }
}
