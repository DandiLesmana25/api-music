<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; //memanggil model user
use App\Models\Log;
use Firebase\JWT\JWT; //memanggil library JWT
use Illuminate\Support\Facades\Validator; //panggil library validator untuk validasi inputan
use Illuminate\Support\Facades\Auth; //panggil library untuk otrntikasi
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthController extends Controller
{



    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password'
        ]);

        // kondisi ketika satu atau lebih inputan tidak sesuai aturan di atas
        // ($validator->validated());
        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        $user = $validator->validated();


        //masukkan user ke database user 
        User::create($user);

        // isi token JWT
        $playload = [
            'name' => $user['name'],
            'role' => 'user',
            'iat' => now()->timestamp,
            'exp' => now()->timestamp + 7200
        ];

        // generate token dengan algoritma HS256
        $token = JWT::encode($playload, env('JWT_SECRET_KEY'), 'HS256');

        // BUAT LOGIN 
        Log::create([
            'module' => 'login',
            'action' => 'login akun',
            'useraccess' => $user['email']
        ]);

        // kirim respons ke pengguna 
        return response()->json([
            "data" => [
                'msg' => "Berhasil Register",
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => 'user',
            ],
            "token" => "Beare {$token}"
        ], 200);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        if (Auth::attempt($validator->validated())) {

            $playload = [
                'name' => Auth::user()->name,
                'role' => Auth::user()->role,
                'iat' => now()->timestamp,
                'id_login' => Auth::user()->id,
            ];

            $token = JWT::encode($playload, env('JWT_SECRET_KEY'), 'HS256');

            Log::create([
                'module' => 'login',
                'action' => 'login akun',
                'useraccess' => Auth::user()->email
            ]);

            return response()->json([
                "data" => [
                    'msg' => "berhasil login",
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'role' => Auth::user()->role,
                ],
                "token" => "Bearer {$token}"
            ], 200);
        }

        return response()->json("email atau password salah", 422);
    }
}


// cekmerge