<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\User;
use App\Models\Song;
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
                'message' => "user id : {$decode->id_login}",
                'user' => $user
            ]
        ], 200);
    }

    //Update akun 
    public function update_register(Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode(
            $jwt,
            new Key(env('JWT_SECRET_KEY'), 'HS256')
        ); //decode token

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

            $data = $request->only([
                'name', 'password', 'email'
            ]);

            if ($request->has('password')) {
                $data['password'] = Hash::make($request->input('password'));
            }

            $user = User::find($decode->id_login);
            $user->updated_at = Carbon::now();
            $user->save();

            $user->update($data);

            return response()->json([
                'data' => [
                    "message" => 'id ' . $decode->id_login . ' berhasil diupdate',
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


    public function request_creator(Request $request)
    {
        $jwt = $request->bearerToken(); // Ambil token

        $decode = JWT::decode(
            $jwt,
            new Key(env('JWT_SECRET_KEY'), 'HS256')
        ); // Decode token

        $user = User::find($decode->id_login);

        if (!$user) {
            return response()->json([
                "data" => [
                    'message' => 'id : ' . $decode->id_login . ' tidak ditemukan'
                ]
            ], 422);
        }

        // Ubah nilai kolom req_upgrade
        $user->req_upgrade = 'request';
        $user->save();

        return response()->json([
            'data' => [
                "message" => 'Berhasil request creator',
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 200);
    }


    //*********************************** U S E R   M A N A  G E M E N T ********************************//


    //Putar lagu
    public function songs_index_id($id)
    {

        $song = Song::find($id);

        if (!$song) {
            return response()->json(
                [
                    'message' => 'Lagu Tidak di Temukan',
                    'statusCode' => 404,
                ],
                404
            );
        }

        return response()->json([
            'message' => 'Lagu dengan id : ' . $id,
            'statusCode' => 200,
            'data' => $song,
        ], 200);
    }
}
