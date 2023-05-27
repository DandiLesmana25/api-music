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
use App\Models\Playlist;
use App\Models\DetailPlaylist;
use App\Models\CreatorRequest;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{


    //*********************************** M U S I C   M A N A G E M E N T *******************************//


    public function songs_index_id($id, Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decode token

        $song = Song::find($id);

        if (!$song) {
            return response()->json(
                [
                    "status" => "error",
                    "code" => 404,
                    'message' => 'Lagu Tidak di Temukan',
                ],
                404
            );
        }

        // BUAT LOGIN 
        ViewSong::create([
            'songs_id' => $id,
            'users_id' => $decode->id_login,
        ]);

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Lagu dengan id : ' . $id,
            'data' => $song,
        ], 200);
    }


    public function last_play(Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decode token

        $latestSongs = ViewSong::where('users_id', $decode->id_login)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->pluck('songs_id');

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
            ->groupBy('songs_id')
            ->orderByRaw('COUNT(*) DESC')
            ->take(5)
            ->pluck('songs_id');

        $songs = Song::whereIn('id', $popularSongs)->get();

        if ($songs->isEmpty()) {
            return response()->json(
                [
                    "status" => "error",
                    "code" => 404,
                    'message' => 'Lagu tidak di temukan',
                ],
                404
            );
        }

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => '5 Lagu terpopuler minggu ini',
            'data' => $songs,
        ], 200);
    }

    //*********************************** M U S I C   M A N A G E M E N T *******************************//



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




    public function albums_index()
    {
        $albums = Album::all();

        if ($albums->isEmpty()) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Tidak ada album yang ditemukan',
            ], 404);
        }

        return response()->json(
            [
                "status" => "success",
                "code" => 200,
                'message' => 'Daftar album',
                'data' => $albums,
            ],
            200
        );
    }



    public function albums_index_id($id)
    {

        $album = Album::find($id);
        $songs = Song::find($id);

        if (!$album) {
            return response()->json(
                [
                    "status" => "success",
                    "code" => 404,
                    'message' => 'Album Tidak di Temukan',
                ],
                404
            );
        }

        return response()->json([
            "status" => 'success',
            "code" => 200,
            "message" => 'Album dengan id : ' . $id,
            "data" => [
                "album" => $album,
                "songs" => $songs,
            ]
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $songs = Song::where('songs_title', 'LIKE', '%' . $keyword . '%')
            ->orWhere('users_name', 'LIKE', '%' . $keyword . '%')
            ->join('users', 'songs.users_id', '=', 'users.id')
            ->select('songs.*', 'users.users_name')
            ->get();

        return response()->json($songs);
    }


    public function create_playlist(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nama' => 'required|string',
                'cover' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|in:private,public',
            ]
        );

        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data tidak valid',
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        $cover = $request->file('cover');
        $coverExtension = $cover->getClientOriginalExtension();
        $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
        $coverPath = 'playlist/' . $coverName;
        $cover->move(public_path('playlist'), $coverPath);

        $playlist = Playlist::create([
            'playlists_name' => $request->input('nama'),
            'playlists_cover' => asset($coverPath),
            'playlists_status' => $request->input('status'),
            'users_id' => $decode->id_login,
        ]);

        return response()->json([
            'message' => 'Playlist created',
            'data' => $playlist,
        ]);
    }



    public function show_all_playlist(Request $request)
    {
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        // Mengambil semua daftar putar dengan users_id yang sesuai
        $playlists = Playlist::where('users_id', $decode->id_login)->get();


        if ($playlists->isNotEmpty()) {

            $playlistData = $playlists->toArray();

            return response()->json([
                'message' => 'Daftar putar berhasil ditemukan',
                'data' => $playlistData
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada daftar putar yang ditemukan',
                'data' => []
            ]);
        }
    }


    public function show_playlist($id, Request $request)
    {
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(
            env('JWT_SECRET_KEY'),
            'HS256'
        ));

        $playlist = Playlist::where(
            'users_id',
            $decode->id_login
        )->find($id);
        $detail_playlist = DetailPlaylist::where('detail_playlist_playlists_id', $id)->get();
        $songIds = $detail_playlist->pluck('detail_playlist_song_id');
        $songs = Song::whereIn('id', $songIds)->get();

        if (!$playlist) {
            return response()->json(
                [
                    'message' => 'Playlist tidak ditemukan',
                    'statusCode' => 404,
                ],
                404
            );
        }

        return response()->json([
            'message' => 'Playlist dengan id: ' . $id,
            'statusCode' => 200,
            'data' => $playlist,
            'songs' => $songs,
        ], 200);
    }



    public function edit_playlist(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'gambar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:private,public',
        ]);

        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "code" => 400,
                "message" => $validator->errors(),
            ], 400);
        }

        $playlist = Playlist::where('users_id', $decode->id_login)->find($id);

        if (!$playlist) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                "message" => "Playlist tidak ditemukan",
            ], 404);
        }

        // Mengupdate nama playlist
        $playlist->playlists_name = $request->input('nama');

        // Mengupdate status playlist
        $playlist->playlists_status = $request->input('status');

        // Mengupdate gambar playlist jika ada
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverExtension = $cover->getClientOriginalExtension();
            $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
            $coverPath = 'playlist/' . $coverName;
            $cover->move(public_path('playlist'), $coverPath);

            // Menghapus file cover lama jika ada
            if ($playlist->playlists_cover) {
                $oldCoverPath = public_path('playlist/' . basename($playlist->playlists_cover));
                if (file_exists($oldCoverPath)) {
                    unlink($oldCoverPath);
                }
            }

            $playlist->playlists_cover = url($coverPath);
        }

        // Menyimpan perubahan pada playlist
        $playlist->save();

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Playlist diupdate',
            'data' => $playlist,
        ]);
    }




    public function delete_playlist($id)
    {
        $playlist = Playlist::find($id);

        if (!$playlist) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                "message" => "Playlist tidak ditemukan",
            ], 404);
        }

        if ($playlist->playlists_cover) {
            $oldCoverPath = public_path('playlist/' . basename($playlist->playlists_cover));
            if (file_exists($oldCoverPath)) {
                unlink($oldCoverPath);
            }
        }

        $playlist->delete();

        return response()->json([
            "data" => [
                "message" => "Playlist berhasil dihapus",
                "id" => $id
            ]
        ], 200);
    }



    public function add_to_playlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|exists:playlists,id',
            'song_id' => 'required|exists:songs,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "code" => 400,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors(),
            ], 400);
        }

        $detailPlaylist = DetailPlaylist::create([
            'detail_playlist_playlists_id' => $request->input('playlist_id'),
            'detail_playlist_song_id' => $request->input('song_id'),
        ]);

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Lagu berhasil di tambahkan ke playlist',
            'data' => $detailPlaylist,
        ]);
    }



    public function remove_from_playlist(Request $request, $playlist_id, $song_id)
    {
        $validator = Validator::make([
            'playlist_id' => $playlist_id,
            'song_id' => $song_id,
        ], [
            'playlist_id' => 'required|exists:playlists,id',
            'song_id' => 'required|exists:songs,id',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "status" => "error",
                    "code" => 400,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors(),
                ],
                400
            );
        }

        $detailPlaylist = DetailPlaylist::where('detail_playlist_playlists_id', $playlist_id)
            ->where('detail_playlist_song_id', $song_id)
            ->first();

        if (!$detailPlaylist) {
            return response()->json(
                [
                    "status" => "error",
                    "code" => 404,
                    'message' => 'Lagu tidak ditemukan dalam playlist',
                ],
                404
            );
        }

        $detailPlaylist->delete();

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Lagu berhasil dihapus dari playlist',
        ]);
    }
}
