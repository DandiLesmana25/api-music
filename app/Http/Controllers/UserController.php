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
use App\Models\ViewSong;
use App\Models\Album;
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



    //*********************************** M U S I C   M A N A G E M E N T *******************************//

    //Putar lagu
    public function songs_index_id($id, Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decode token

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

        // BUAT LOGIN 
        ViewSong::create([
            'id_lagu' => $id,
            'id_user' => $decode->id_login,
        ]);

        return response()->json([
            'message' => 'Lagu dengan id : ' . $id,
            'statusCode' => 200,
            'data' => $song,
        ], 200);
    }


    public function last_play(Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decode token

        $latestSongs = ViewSong::where('id_user', $decode->id_login)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->pluck('id_lagu');

        $songs = Song::whereIn('id', $latestSongs)->get();

        if ($songs->isEmpty()) {
            return response()->json(
                [
                    'message' => 'Tidak ada lagu terbaru yang diputar',
                    'statusCode' => 404,
                ],
                404
            );
        }

        return response()->json([
            'message' => '5 lagu terbaru yang terakhir diputar oleh pengguna',
            'statusCode' => 200,
            'data' => $songs,
        ], 200);
    }


    public function trending(Request $request)
    {
        $mondayLastWeek = Carbon::now()->subWeek()->startOfWeek()->addDay(); // Ambil hari Senin satu minggu yang lalu

        $popularSongs = ViewSong::where('created_at', '>', $mondayLastWeek)
            ->groupBy('id_lagu')
            ->orderByRaw('COUNT(*) DESC')
            ->take(5)
            ->pluck('id_lagu');

        $songs = Song::whereIn('id', $popularSongs)->get();

        if ($songs->isEmpty()) {
            return response()->json(
                [
                    'message' => 'Tidak ada lagu yang paling banyak diputar dalam satu minggu terakhir',
                    'statusCode' => 404,
                ],
                404
            );
        }

        return response()->json([
            'message' => '5 lagu yang paling banyak diputar dalam satu minggu terakhir',
            'statusCode' => 200,
            'data' => $songs,
        ], 200);
    }

    //*********************************** M U S I C   M A N A G E M E N T *******************************//


    public function albums_index()
    {
        //

        $albums = Album::all();

        return response()->json([
            'message' => 'Berhasil menampilkan daftar album',
            'statusCode' => 200,
            'data' => $albums,
        ], 200);
    }


    public function albums_index_id($id)
    {

        $album = Album::find($id);
        $songs = Song::find($id);

        if (!$album) {
            return response()->json(
                [
                    'message' => 'Album Tidak di Temukan',
                    'statusCode' => 404,
                ],
                404
            );
        }

        return response()->json([
            'message' => 'Album dengan id : ' . $id,
            'statusCode' => 200,
            'data' => $album,
            'songs' => $songs,
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $songs = Song::where('judul', 'LIKE', '%' . $keyword . '%')
            ->orWhere('name', 'LIKE', '%' . $keyword . '%')
            ->join('users', 'songs.id_user', '=', 'users.id')
            ->select('songs.*', 'users.name')
            ->get();

        return response()->json($songs);
    }
}
