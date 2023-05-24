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

        return response()->json(["data" => $result]);
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

        // munculkan akun berdasarkan id
        $user = User::find($id);

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
                'users_last_login' => Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    'message' => 'User dengan id ' . $id . ' berhasil diperbarui',
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

        $users = User::where('req_upgrade', 'request')->get();

        return response()->json([
            "data" => [
                'message' => "User yang request menjadi creator",
                'data' => $users
            ]
        ], 200);
    }


    public function approve_creator(Request $request, $id)
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
                "data" => [
                    'message' => 'id : ' . $id . ' tidak ditemukan'
                ]
            ], 422);
        }


        // Ubah nilai kolom req_upgrade
        $user->req_upgrade = 'creator';
        $user->role = 'creator';
        $user->save();

        return response()->json([
            'data' => [
                "message" => 'id ' . $id . ' Berhasil menjadi creator',
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
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

        $user->password = bcrypt('user');
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






    //********************************** M U S I C   M A N A G E M E N T *******************************//

    //Menambah Lagu
    public function add_song(Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decode token

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'cover' => 'required|mimes:png,jpg,jpeg|max:2048',
            'lagu' => 'required|file|mimes:mp3',
            'tanggal_rilis' => 'required|date',
            'status' => 'required|in:pending,published,unpublished',
            // 'id_label' => 'nullable|exists:labels,id',
            'id_album' => 'nullable|exists:albums,id',
            'mood' => 'nullable|in:Bahagia, Sedih, Romantis, Santai, Enerjik, Motivasi, Eksperimental, Sentimental, Menghibur, Gelisah, Inspiratif, Tenang, Semangat, Melankolis, Penuh energi, Memikat, Riang, Reflektif, Optimis, Bersemangat',
            'genre' => 'nullable|in:Pop, Rock, Hip-Hop, R&B, Country, Jazz, Electronic, Dance, Reggae, Folk, Classical, Alternative, Indie, Metal, Punk, Blues, Soul, Funk, Latin, World',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Invalid data',
                    'status' => 400,
                    'errors' => $validator->errors(),
                ],
                400
            );
        }

        $file = $request->file('lagu');
        $laguExtension = $file->getClientOriginalExtension();
        $laguName = uniqid() . '_' . time() . '.' . $laguExtension;
        $laguPath = 'lagu/' . $laguName;
        $file->move(public_path('lagu'), $laguPath);
        $laguUrl = asset($laguPath);

        $cover = $request->file('cover');
        $coverExtension = $cover->getClientOriginalExtension();
        $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
        $coverPath = 'cover/' . $coverName;
        $cover->move(public_path('cover'), $coverPath);
        $coverUrl = asset($coverPath);

        $song = new Song();
        $song->judul = $request->judul;
        $song->cover = $coverUrl;
        $song->lagu = $laguUrl;
        $song->tanggal_rilis = $request->tanggal_rilis;
        $song->status = $request->status ?? 'pending'; // Menggunakan nilai default 'pending' jika status tidak disertakan dalam request
        $song->id_user = $decode->id_login;
        $song->id_album = $request->id_album;
        $song->mood = $request->mood;
        $song->genre = $request->genre;
        $song->save();

        return response()->json(
            [
                'message' => 'Lagu berhasil diunggah',
                'status' => 200,
                'data' => [
                    'judul' => $song->judul,
                    'cover' => $coverUrl,
                    'lagu' => $laguUrl,
                    'tanggal_rilis' => $song->tanggal_rilis,
                    'status' => $song->status,
                    'id_user' => $song->id_user,
                    'id_label' => $song->id_label,
                    'id_album' => $song->id_album,
                    'mood' => $song->mood,
                    'genre' => $song->genre,
                ],
            ],
            200
        );
    }


    //Menampilkan lagu
    public function songs_index()
    {
        //

        $songs = Song::all();

        return response()->json([
            'message' => 'Berhasil menampilkan daftar lagu',
            'statusCode' => 200,
            'data' => $songs,
        ], 200);
    }

    //Menampilkan lagu berdasarkan ID
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

    //Edit lagu
    public function edit_song(Request $request, $id)
    {
        $jwt = $request->bearerToken(); // Ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); // Decode token

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'cover' => 'required|mimes:png,jpg,jpeg|max:2048',
            'lagu' => 'required|file|mimes:mp3',
            'tanggal_rilis' => 'required|date',
            'status' => 'required|in:pending,published,unpublished',
            'id_album' => 'nullable|exists:albums,id',
            'mood' => 'nullable|in:Bahagia, Sedih, Romantis, Santai, Enerjik, Motivasi, Eksperimental, Sentimental, Menghibur, Gelisah, Inspiratif, Tenang, Semangat, Melankolis, Penuh energi, Memikat, Riang, Reflektif, Optimis, Bersemangat',
            'genre' => 'nullable|in:Pop, Rock, Hip-Hop, R&B, Country, Jazz, Electronic, Dance, Reggae, Folk, Classical, Alternative, Indie, Metal, Punk, Blues, Soul, Funk, Latin, World',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'message' => 'Invalid data',
                    'status' => 400,
                    'errors' => $validator->errors(),
                ],
                400
            );
        }

        $song = Song::find($id);

        if (!$song) {
            return response()->json([
                'message' => 'Song not found',
                'status' => 404,
            ], 404);
        }

        // Periksa apakah pengguna memiliki hak akses untuk mengedit lagu
        if ($decode->id_login != $song->id_user) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 401,
            ], 401);
        }

        // Update data lagu
        $song->judul = $request->judul;
        $song->tanggal_rilis = $request->tanggal_rilis;
        $song->status = $request->status;

        // Cek apakah ada file cover yang diunggah
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverExtension = $cover->getClientOriginalExtension();
            $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
            $coverPath = 'cover/' . $coverName;
            $cover->move(public_path('cover'), $coverPath);
            $coverUrl = asset($coverPath);

            // Menghapus file cover lama jika ada
            if ($song->cover && Storage::exists(parse_url($song->cover, PHP_URL_PATH))) {
                Storage::delete(parse_url(
                    $song->cover,
                    PHP_URL_PATH
                ));
            }

            $song->cover = $coverUrl;
        }

        // Cek apakah ada file lagu yang diunggah
        if ($request->hasFile('lagu')) {
            $file = $request->file('lagu');
            $laguExtension = $file->getClientOriginalExtension();
            $laguName = uniqid() . '_' . time() . '.' . $laguExtension;
            $laguPath = 'lagu/' . $laguName;
            $file->move(public_path('lagu'), $laguPath);
            $laguUrl = asset($laguPath);

            // Menghapus file lagu lama jika ada
            if ($song->lagu && Storage::exists(parse_url($song->lagu, PHP_URL_PATH))) {
                Storage::delete(parse_url($song->lagu, PHP_URL_PATH));
            }

            $song->lagu = $laguUrl;
        }

        $song->save();

        return response()->json([
            'message' => 'Lagu berhasil di sunting',
            'status' => 200,
            'data' => [
                'judul' => $song->judul,
                'cover' => $song->cover,
                'lagu' => $song->lagu,
                'tanggal_rilis' => $song->tanggal_rilis,
                'status' => $song->status,
                'id_user' => $song->id_user,
                'id_label' => $song->id_label,
            ],
        ], 200);
    }

    //Hapus lagu
    public function delete_song($id)
    {

        Song::where('id', $id)->delete();

        return response()->json([
            "data" => [
                "message" => "Lagu berhasil di hapus",
                "id" => $id
            ]
        ], 200);
    }

    //********************************** M U S I C   M A N A  G E M E N T *******************************//



    public function add_album(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'cover' => 'required|mimes:png,jpg,jpeg|max:2048',
            'tanggal_rilis' => 'nullable|date',
            'genre' => 'nullable|string',
            'status' => 'nullable|in:private,public',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "code" => 400,
                'message' => $validator->errors(),
            ], 400);
        }

        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $users = User::find($decode->id_login);

        $cover = $request->file('cover');
        $coverExtension = $cover->getClientOriginalExtension();
        $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
        $coverPath = 'album/' . $coverName;
        $cover->move(public_path('album'), $coverPath);

        $album = new Album();
        $album->albums_title = $request->input('judul');
        $album->albums_artist = $decode->name;
        $album->albums_cover = $coverPath;
        $album->albums_release_date = $request->input('tanggal_rilis');
        $album->albums_status = $request->input('status', 'private');
        $album->users_id = $decode->id_login;
        $album->albums_genre = $request->input('genre');
        $album->save();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Album berhasil disimpan',
            'data' => $album,
        ]);
    }




    public function albums_index()
    {
        //

        $albums = Album::all();

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Daftar Album',
            'data' => $albums,
        ], 200);
    }


    public function albums_index_id($id)
    {

        $album = Album::find($id);
        $songs = Song::find($id);
        $user = User::find($album->users_id);

        if (!$album) {
            return response()->json(
                [
                    "status" => "error",
                    "code" => 404,
                    'message' => 'Album Tidak di Temukan',
                ],
                404
            );
        }

        return response()->json([
            "status" => "success",
            "code" => 200,
            "message" => 'Album dengan id : ' . $id,
            "data" => $album,
            "songs" => $songs,
            "user" => $user,
        ], 200);
    }

    public function edit_album(Request $request, $id)
    {
        $jwt = $request->bearerToken(); // Ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); // Decode token

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'cover' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'tanggal_rilis' => 'nullable|date',
            'genre' => 'nullable|string',
            'status' => 'nullable|in:private,public',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "code" => 400,
                'message' => $validator->errors(),
            ], 400);
        }

        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Album tidak ditemukan',
            ], 404);
        }

        // Update data album
        $album->albums_title = $request->input('judul');
        $album->albums_release_date = $request->input('tanggal_rilis');
        $album->albums_genre = $request->input('genre');
        $album->albums_status = $request->input('status');

        // Cek apakah ada file cover yang diunggah
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverExtension = $cover->getClientOriginalExtension();
            $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
            $coverPath = 'album/' . $coverName;
            $cover->move(public_path('album'), $coverPath);
            $coverUrl = asset($coverPath);

            // Menghapus file cover lama jika ada
            if ($album->albums_cover) {
                $oldCoverPath = public_path('album/' . basename($album->albums_cover));
                if (file_exists($oldCoverPath)) {
                    unlink($oldCoverPath);
                }
            }


            $album->albums_cover = $coverUrl;
        }

        $album->save();

        return response()->json([
            'message' => 'Album berhasil diupdate',
            'status' => 200,
            'base' => public_path('album/646e41ab7b4de_1684947371.PNG'),
            'data' => $album,
        ], 200);
    }


    public function delete_album($id)
    {

        Album::where('id', $id)->delete();

        return response()->json([
            "data" => [
                "message" => "Album berhasil di hapus",
                "id" => $id
            ]
        ], 200);
    }
}
