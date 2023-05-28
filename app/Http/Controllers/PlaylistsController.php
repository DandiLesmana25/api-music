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

class PlaylistsController extends Controller
{

    public function create_playlist(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'cover' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|in:private,public',
            ]
        );

        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "code" => 400,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Cek apakah nama playlist sudah ada sebelumnya untuk pengguna yang sama
        $existingPlaylist = Playlist::where('playlists_name', $request->input('name'))
            ->where('users_id', $decode->id_login)
            ->first();

        if ($existingPlaylist) {
            return response()->json([
                'status' => 'error',
                'code' => 409,
                'message' => 'Nama playlist sudah ada',
            ], 409);
        }

        $cover = $request->file('cover');
        $coverExtension = $cover->getClientOriginalExtension();
        $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
        $coverPath = 'playlist/' . $coverName;
        $cover->move(public_path('playlist'), $coverPath);

        $playlist = Playlist::create([
            'playlists_name' => $request->input('name'),
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

        $userRole = $decode->role; // Mengambil role dari token JWT

        if ($userRole === 'admin') {
            // Jika role adalah admin, dapatkan semua daftar putar
            $playlists = Playlist::all();
        } else {
            // Jika role adalah user atau creator, dapatkan daftar putar yang sesuai dengan users_id
            $playlists = Playlist::where(function ($query) use ($decode) {
                $query->where('users_id', $decode->id_login)
                    ->orWhere('playlists_status', 'public');
            })->get();
        }

        if ($playlists->isNotEmpty()) {
            $playlistData = $playlists->toArray();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Daftar putar berhasil ditemukan',
                'data' => $playlistData
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Tidak ada daftar putar yang ditemukan',
                'data' => []
            ], 404);
        }
    }



    public function show_playlist($id, Request $request)
    {
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $userRole = $decode->role; // Mengambil role dari token JWT

        if ($userRole === 'admin') {
            // Jika role adalah admin, dapatkan playlist tanpa memeriksa users_id
            $playlist = Playlist::find($id);
        } else {
            // Jika role adalah user atau creator, dapatkan playlist sesuai dengan users_id atau playlist dengan status "public"
            $playlist = Playlist::where(function ($query) use ($decode, $id) {
                $query->where('users_id', $decode->id_login)
                    ->orWhere(function ($query) use ($id) {
                        $query->where('playlists_status', 'public')
                            ->where('id', $id);
                    });
            })->first();
        }

        if (!$playlist) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Playlist tidak ditemukan',
            ], 404);
        }

        $detail_playlist = DetailPlaylist::where('detail_playlist_playlists_id', $id)->get();
        $songIds = $detail_playlist->pluck('detail_playlist_song_id');
        $songs = Song::whereIn('id', $songIds)->get();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Playlist dengan id: ' . $id,
            'data' => $playlist,
            'songs' => $songs,
        ], 200);
    }





    public function edit_playlist(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'cover' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
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

        $userRole = $decode->role; // Mengambil role dari token JWT

        if ($userRole === 'admin') {
            // Jika role adalah admin, dapatkan playlist tanpa memeriksa users_id
            $playlist = Playlist::find($id);
        } else {
            // Jika role adalah user atau creator, dapatkan playlist sesuai dengan users_id
            $playlist = Playlist::where('users_id', $decode->id_login)->find($id);
        }

        if (!$playlist) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                "message" => "Playlist tidak ditemukan",
            ], 404);
        }

        // Mengupdate nama playlist
        $playlist->playlists_name = $request->input('name');

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





    public function delete_playlist($id, Request $request)
    {
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $userRole = $decode->role; // Mengambil role dari token JWT

        if ($userRole === 'admin') {
            // Jika role adalah admin, dapatkan playlist tanpa memeriksa users_id
            $playlist = Playlist::find($id);
        } else {
            // Jika role adalah user atau creator, dapatkan playlist sesuai dengan users_id
            $playlist = Playlist::where('users_id', $decode->id_login)->find($id);
        }

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
            "status" => "success",
            "code" => 200,
            "message" => "Playlist berhasil dihapus",
            "data" => [
                $playlist
            ]
        ], 200);
    }




    public function add_to_playlist(Request $request)
    {
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $playlist = Playlist::where('users_id', $decode->id_login)
            ->find($request->input('playlist_id'));

        if (!$playlist) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Playlist tidak ditemukan',
            ], 404);
        }

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

        $playlistId = $request->input('playlist_id');
        $songId = $request->input('song_id');

        $existingDetailPlaylist = DetailPlaylist::where('detail_playlist_playlists_id', $playlistId)
            ->where('detail_playlist_song_id', $songId)
            ->first();

        if ($existingDetailPlaylist) {
            return response()->json([
                "status" => "error",
                "code" => 409,
                'message' => 'Lagu sudah ada dalam playlist',
            ], 409);
        }

        $detailPlaylist = DetailPlaylist::create([
            'detail_playlist_playlists_id' => $playlistId,
            'detail_playlist_song_id' => $songId,
        ]);

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Lagu berhasil ditambahkan ke playlist',
            'data' => $detailPlaylist,
        ]);
    }



    public function remove_from_playlist(Request $request, $playlist_id, $song_id)
    {
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $playlist = Playlist::where('users_id', $decode->id_login)
            ->find($playlist_id);

        if (!$playlist) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Playlist tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make([
            'playlist_id' => $playlist_id,
            'song_id' => $song_id,
        ], [
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

        $detailPlaylist = DetailPlaylist::where('detail_playlist_playlists_id', $playlist_id)
            ->where('detail_playlist_song_id', $song_id)
            ->first();

        if (!$detailPlaylist) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Lagu tidak ditemukan dalam playlist',
            ], 404);
        }

        $detailPlaylist->delete();

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Lagu berhasil dihapus dari playlist',
        ]);
    }
}
