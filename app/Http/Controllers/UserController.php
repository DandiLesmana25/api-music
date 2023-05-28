<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\User;
use App\Models\CreatorRequest;

class UserController extends Controller
{



    //*********************************** U S E R   M A N A  G E M E N T ********************************//


    public function show_register_by_id(Request $request)
    {

        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decoce token

        // munculkan akun berdasarkan id
        $user = User::find($decode->id_login);

        return response()->json([
            "data" => [
                'message' => "user id : {$decode->id_login}",
                'user' => $user
            ]
        ], 200);
    }


    public function update_register(Request $request)
    {
        $jwt = $request->bearerToken(); // Ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); // Decode token

        $user = User::find($decode->id_login);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'min:8',
                'confirmation_password' => 'same:password',
                'email' => 'email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'code' => 422,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'users_name' => $request->input('name'),
                'users_email' => $request->input('email')
            ];

            if ($request->has('password')) {
                $data['users_password'] = Hash::make($request->input('password'));
            }

            $user->update($data); // Menggunakan metode update() untuk memperbarui data

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Data pengguna berhasil diperbarui',
                'data' => $user
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'code' => 404,
            'message' => 'Pengguna tidak ditemukan'
        ], 404);
    }




    public function request_creator(Request $request)
    {
        $jwt = $request->bearerToken(); // Ambil token

        $decode = JWT::decode(
            $jwt,
            new Key(env('JWT_SECRET_KEY'), 'HS256')
        ); // Decode token

        // Cek apakah data request sudah ada sebelumnya
        $existingRequest = CreatorRequest::where('users_id', $decode->id_login)->first();

        if ($existingRequest) {
            if ($existingRequest->status === 'approved') {
                return response()->json(
                    [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Anda sudah di-approve sebagai creator'
                    ],
                    200
                );
            } elseif ($existingRequest->status === 'rejected') {
                return response()->json([
                    'status' => 'error',
                    'code' => 422,
                    'message' => 'Anda telah ditolak sebagai creator'
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Anda telah mengirim permintaan sebelumnya'
            ], 400);
        }

        // Ambil inputan remarks dari request jika ada
        $remarks = $request->input('remarks');

        // Simpan data request ke tabel creator_requests
        $creatorRequest = new CreatorRequest();
        $creatorRequest->users_id = $decode->id_login;
        $creatorRequest->request_date = now();
        $creatorRequest->status = 'request';
        $creatorRequest->remarks = $remarks; // Set inputan remarks
        $creatorRequest->save();

        return response()->json([
            'data' => [
                'status' => 'success',
                'code' => 200,
                'message' => 'Berhasil request creator',
                'data' => [
                    'name' => $decode->name,
                    'email' => $decode->email,
                ]
            ]
        ], 200);
    }


    //*********************************** U S E R   M A N A  G E M E N T ********************************//



}
