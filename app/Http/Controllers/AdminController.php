<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\Song;
use App\Models\User;
use App\Models\Album;
use App\Models\User_Deleted;
use App\Models\CreatorRequest;
use Illuminate\Session\Store;


class AdminController extends Controller

{

    //**************************************** D A S H B O A R D *****************************************//

    public function dashboard()
    {
        $userCount = User::where('users_role', 'user')->count();
        $creatorCount = User::where('users_role', 'creator')->count();

        $startDate = now()->subWeek()->startOfDay();
        $endDate = now()->endOfDay();

        $songCount = Song::whereBetween('songs_release_date', [$startDate, $endDate])->count();
        $userUpdate = User::whereBetween('created_at', [$startDate, $endDate])->count();

        $result = [
            "user_statistics" => [
                'user_count' => $userCount,
                'creator_count' => $creatorCount,
            ],
            "recent_activity" => [
                'song_count' => $songCount,
                'user_count' => $userUpdate,
            ]
        ];

        return response()->json($result);
    }


    //**************************************** D A S H B O A R D *****************************************//




    //*********************************** U S E R   M A N A  G E M E N T ********************************//

    //Registrasi akun via admin

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,users_email',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password',
            'role' => 'required|in:user,creator,admin',
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
            'users_role' => $userData['role'],
            'users_password' => Hash::make($userData['password']),
        ]);

        $token = User::generateToken($user->users_name, $user->users_email, $user->users_role, $user->id);

        return response()->json(
            [
                "status" => "success",
                "code" => 200,
                "message" => "Berhasil Registrasi",
                "data" => [
                    'id' => $user->id,
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                ],
                "token" => "Bearer {$token}"
            ],
            200
        );
    }




    //Menampilkan akun terregisrasi
    public function show_register()
    {
        // Mencari semua akun dengan role user atau creator
        $users = User::whereIn('users_role', ['user', 'creator'])->get();

        return response()->json([
            "data" => [
                'message' => "User registration",
                'data' => $users
            ]
        ], 200);
    }

    //Menampilkan akun berdasarkan Id
    public function show_register_by_id($id)
    {
        // Mencari akun berdasarkan id
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                "message" => "Data user tidak ditemukan"
            ], 404);
        }

        return response()->json([
            "status" => "success",
            "code" => 200,
            "message" => "Data user {$id}",
            "data" => $user
        ], 200);
    }


    //Update akun via admin
    public function update_register(Request $request, $id)
    {
        $user = User::find($id);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'nullable|min:8',
                'confirmation_password' => 'same:password',
                'email' => 'required|email|unique:users,users_email,' . $id,
                'role' => 'required|in:admin,user,creator',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'code' => 400,
                    'messages' => $validator->messages()
                ], 400);
            }

            $userData = $validator->validated();

            User::where('id', $id)->update([
                'users_name' => $userData['name'],
                'users_email' => $userData['email'],
                'users_role' => $userData['role'],
                'users_password' => bcrypt($userData['password']),
            ]);

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'User dengan id ' . $id . ' berhasil diperbarui',
                'data' => [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'code' => 422,
            'data' => [
                'message' => 'User dengan id ' . $id . ' tidak ditemukan'
            ]
        ], 422);
    }


    //Hapus akun via admin
    public function delete_register($id, Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decoce token

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                "message" => "User tidak ditemukan",
            ], 404);
        }

        User_Deleted::create([
            'users_deleted_name' => $user->users_name,
            'users_deleted_email' => $user->users_email,
            'users_deleted_deleted_by' => $decode->id_login
        ]);


        $user->delete();

        return response()->json([
            "status" => "success",
            "code" => 200,
            "message" => 'User berhasil di hapus',
            "data" => $user
        ], 200);
    }


    //Menampilkan akun yang request creator
    public function request_creator()
    {
        $users = CreatorRequest::where('status', 'request')->get();

        if ($users->isEmpty()) {
            return response()->json([
                "data" => [
                    'message' => "Tidak ada pengguna yang meminta menjadi creator",
                    'data' => []
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'message' => "User yang meminta menjadi creator",
                'data' => $users
            ]
        ], 200);
    }



    public function approve_creator(Request $request, $id)
    {
        $jwt = $request->bearerToken(); // Ambil token

        $decode = JWT::decode(
            $jwt,
            new Key(env('JWT_SECRET_KEY'), 'HS256')
        ); // Decode token

        $creatorRequest = CreatorRequest::find($id);

        if (!$creatorRequest) {
            return response()->json([
                'data' => [
                    'message' => 'Permintaan dengan id: ' . $id . ' tidak ditemukan'
                ]
            ], 422);
        }

        $user = User::find($creatorRequest->users_id);

        if (!$user) {
            return response()->json([
                'data' => [
                    'message' => 'User dengan id: ' . $creatorRequest->users_id . ' tidak ditemukan'
                ]
            ], 422);
        }

        $user->users_role = 'creator';
        $user->save();

        $creatorRequest->status = 'approved';
        $creatorRequest->save();

        return response()->json([
            'data' => [
                'message' => 'User dengan id ' . $user->id . ' berhasil menjadi creator',
                'name' => $user->users_name,
                'email' => $user->users_email,
                'role' => $user->users_role,
            ]
        ], 200);
    }




    public function reset_password(Request $request, $id)
    {
        $jwt = $request->bearerToken(); // Ambil token

        $decode = JWT::decode(
            $jwt,
            new Key(
                env('JWT_SECRET_KEY'),
                'HS256'
            )
        ); // Decode token

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                "status" => "error",
                "code" => 422,
                "message" => "User tidak di temukan",
            ], 422);
        }

        $user->users_password = bcrypt('user');
        $user->save();

        return response()->json([
            "status" => "success",
            "code" => 200,
            "message" => 'id ' . $id . ' Berhasil reset  password',
            'data' => [
                'name' => $user->users_name,
                'email' => $user->users_email,
                'password' => 'user',
                'role' => $user->users_role,
            ]
        ], 200);
    }

    //*********************************** U S E R   M A N A G E M E N T ********************************//



}
