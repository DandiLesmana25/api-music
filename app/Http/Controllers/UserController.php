<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\User;
use App\Models\User_Deleted;

class UserController extends Controller
{

    //*********************************** U S E R   M A N A  G E M E N T ********************************//


    //Menampilkan akun berdasarkan Id
    public function show_register_by_id(Request $request)
    {

        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decoce token

        // munculkan akun berdasarkan id
        $user = User::find($decode->id_login);

        return response()->json([
            "data" => [
                'message' => "user id:{$decode->id_login}",
                'data' => $user
            ]
        ], 200);
    }

    //Update akun via admin
    public function update_register(Request $request)
    {

        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decoce token

        $user = User::find($decode->id_login);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'min:8',
                'confirmation_password' => 'same:password',
                'email' => 'email'
            ]);

            if ($validator->fails()) {
                return messageError($validator->messages()->toArray());
            }

            $data = $request->only(['name', 'password', 'email']);

            User::where('id', $decode->id_login)->update($data);

            return response()->json([
                'data' => [
                    "message" => 'id : ' . $decode->id_login . ' berhasil diupdate',
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'message' => 'id : ' . $decode->id_login . ' tidak ditemukan'
            ]
        ], 422);
    }



    //Hapus akun via admin
    public function delete_register(Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decoce token

        $user = User::find($decode->id_login);

        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        User_Deleted::create([
            'name' => $user->name,
            'email' => $user->email,
            'deleted_by' => $decode->id_login
        ]);

        $user->delete();

        return response()->json([
            'message' => 'User berhasil di hapus'
        ], 200);
    }

    //*********************************** U S E R   M A N A  G E M E N T ********************************//
}
