<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\Song;
use App\Models\User;
use App\Models\User_Deleted;



class AdminController extends Controller

{

    //**************************************** D A S H B O A R D *****************************************//

    public function dashboard()
    {
        $userCount = User::where('role', 'user')->count();
        $creatorCount = User::where('role', 'creator')->count();

        $startDate = now()->subWeek()->startOfDay();
        $endDate = now()->endOfDay();

        $songCount = Song::whereBetween('tanggal_rilis', [$startDate, $endDate])->count();
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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password',
            'role' => 'required|in:admin,user,creator',
        ]);

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }
        $user = $validator->validated();

        User::create($user);

        return response()->json([
            "data" => [
                'message' => "Akun berhasil di buat",
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 200);
    }

    //Menampilkan akun terregisrasi
    public function show_register()
    {
        // Mencari semua akun dengan role user atau creator
        $users = User::whereIn('role', ['user', 'creator'])->get();

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
            "data" => [
                'message' => "user id:{$id}",
                'data' => $user
            ]
        ], 200);
    }

    //Update akun via admin
    public function update_register(Request $request, $id)
    {
        $user = User::find($id);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'min:8',
                'confirmation_password' => 'same:password',
                'email' => 'email',
                'role' => 'required|in:admin,user,creator',
            ]);

            if ($validator->fails()) {
                return messageError($validator->messages()->toArray());
            }

            $data = $request->only(['name', 'email', 'role']);

            if ($request->has('password')) {
                $data['password'] = bcrypt($request->password);
            }

            User::where('id', $id)->update($data);

            return response()->json([
                'data' => [
                    "message" => 'User dengan id ' . $id . ' berhasil diupdate',
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
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
                'error' => 'User tidak di temukan'
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
                "data" => [
                    'message' => 'id : ' . $id . ' tidak ditemukan'
                ]
            ], 422);
        }


        // Ubah nilai kolom req_upgrade
        $user->password = bcrypt('user');
        $user->save();

        return response()->json([
            'data' => [
                "message" => 'id ' . $id . ' Berhasil reset  password',
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 200);
    }

    //*********************************** U S E R   M A N A  G E M E N T ********************************//






    //********************************** M U S I C   M A N A  G E M E N T *******************************//

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
            // 'id_user' => 'required|exists:users,id',
            'id_label' => 'nullable|exists:labels,id',
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
        $song->id_label = $request->id_label;
        $song->save();

        return response()->json(
            [
                'message' => 'Lagu berhasil di unggah',
                'status' => 200,
                'data' => [
                    'judul' => $song->judul,
                    'cover' => $coverUrl,
                    'lagu' => $laguUrl,
                    'tanggal_rilis' => $song->tanggal_rilis,
                    'status' => $song->status,
                    'id_user' => $song->id_user,
                    'id_label' => $song->id_label,
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
            'cover' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'lagu' => 'nullable|file|mimes:mp3',
            'tanggal_rilis' => 'required|date',
            'status' => 'required|in:pending,published,unpublished',
            'id_label' => 'nullable|exists:labels,id',
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
        $song->id_label = $request->id_label;

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
            'message' => 'Song updated successfully',
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

}
