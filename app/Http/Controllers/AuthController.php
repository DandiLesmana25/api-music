<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; //memanggil model user
use App\Models\Log;
use Firebase\JWT\JWT; //memanggil library JWT
use Illuminate\Support\Facades\Validator; //panggil library validator untuk validasi inputan
use Illuminate\Support\Facades\Auth; //panggil library untuk otrntikasi
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{



    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,users_email',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'messages' => $validator->messages()
            ], 400);
        }

        $userData = $validator->validated();

        $user = User::create([
            'users_name' => $userData['name'],
            'users_email' => $userData['email'],
            'users_password' => bcrypt($userData['password']),
            'users_last_login' => Carbon::now(),
        ]);

        $payload = [
            'name' => $userData['name'],
            'role' => 'user',
            'iat' => now()->timestamp,
        ];

        $token = JWT::encode($payload, env('JWT_SECRET_KEY'), 'HS256');

        // Log::create([
        //     'logs_module' => 'register',
        //     'logs_action' => 'register account',
        //     'users_id' => $user->id
        // ]);

        return response()->json(
            [
                "status" => "success",
                "code" => 200,
                "message" => "Berhasil Registrasi",
                "data" => [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => 'user',
                ],
                "token" => "Bearer {$token}"
            ],
            200
        );
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'messages' => $validator->messages()
            ], 400);
        }

        $user = User::where('users_email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->users_password)) {
            $playload = [
                'name' => $user->users_name,
                'role' => $user->users_role,
                'iat' => now()->timestamp,
                'id_login' => $user->id,
            ];

            $token = JWT::encode($playload, env('JWT_SECRET_KEY'), 'HS256');

            Log::create([
                'logs_module' => 'login',
                'logs_action' => 'login',
                'users_id' => $user->id
            ]);

            $user->users_last_login = Carbon::now();
            $user->save();

            return response()->json([
                "status" => "success",
                "code" => 200,
                "message" => "Berhasil Login",
                "data" => [
                    'id' => $user->id,
                    'name' => $user->users_name,
                    'email' => $user->users_email,
                    'role' => $user->users_role,
                ],
                "token" => "Bearer {$token}"
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'code' => 422,
            'message' => 'Email atau password salah'
        ], 422);
    }
}
