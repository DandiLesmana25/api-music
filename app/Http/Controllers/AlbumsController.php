<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\User;
use App\Models\Song;
use App\Models\Album;


class AlbumsController extends Controller
{



    public function add_album(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'cover' => 'required|mimes:png,jpg,jpeg|max:2048',
            'release_date' => 'nullable|date',
            'genre' => 'nullable|string',
            'status' => 'nullable|in:private,public',
        ]);

        $validator->after(function ($validator) use ($request) {
            $existingAlbum = Album::where('albums_title', $request->input('title'))->first();
            if ($existingAlbum) {
                $validator->errors()->add('title', 'Album dengan nama yang sama sudah ada');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => $validator->errors(),
            ], 400);
        }

        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $cover = $request->file('cover');
        $coverExtension = $cover->getClientOriginalExtension();
        $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
        $coverPath = 'albums/' . $coverName;
        $cover->move(public_path('albums'), $coverPath);

        $album = new Album();
        $album->albums_title = $request->input('title');
        $album->albums_artist = $decode->name;
        $album->albums_cover = url($coverPath);
        $album->albums_release_date = $request->input('release_date');
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
        $jwt = request()->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $user = User::find($decode->id_login);

        if ($user->users_role === 'admin') {
            $albums = Album::all();
        } else {
            $albums = Album::where(function ($query) use ($decode) {
                $query->where('users_id', $decode->id_login)
                    ->orWhere('albums_status', 'public'); // Menambahkan kondisi albums_status = public
            })->get();
        }

        if ($albums->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Tidak ada album yang ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Daftar album',
            'data' => $albums,
        ], 200);
    }







    public function albums_index_id($id)
    {
        $jwt = request()->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $album = Album::find($id);

        if (!$album) {
            return response()->json(
                [
                    "status" => "error",
                    "code" => 404,
                    'message' => 'Album Tidak ditemukan',
                ],
                404
            );
        }

        // Memeriksa apakah pengguna yang sedang mengakses adalah admin
        if ($decode->role === 'admin') {
            // Mengambil semua lagu untuk admin
            $songs = Song::where('albums_id', $id)->get();
        }
        if ($decode->role === 'creator') {
            $songs = Song::where('albums_id', $id)->get();
            $songs = Song::where('users_id', $decode->id_login)->get();
        } else {
            // Memeriksa apakah album memiliki status "public"
            if ($album->albums_status !== 'public') {
                return response()->json(
                    [
                        "status" => "error",
                        "code" => 403,
                        'message' => 'Akses ditolak',
                    ],
                    403
                );
            }

            // Mengambil lagu-lagu dengan status "published" atau "pending" jika pengguna adalah "user" atau "creator"
            if ($decode->role === 'user' || $decode->role === 'creator') {
                $songs = Song::where('albums_id', $id)
                    ->whereIn('songs_status', ['published', 'pending'])
                    ->get();
            } else {
                // Mengambil semua lagu
                $songs = Song::where('albums_id', $id)->get();
            }
        }

        $user = User::find($album->users_id);

        return response()->json([
            "status" => "success",
            "code" => 200,
            "message" => 'Album dengan id: ' . $id,
            "data" => $album,
            "songs" => $songs,
            "author" => $user,
        ], 200);
    }














    public function edit_album(Request $request, $id)
    {
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'cover' => 'nullable|mimes:png,jpg,jpeg|max:2048',
            'release_date' => 'nullable|date',
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

        // Memeriksa otorisasi
        if ($decode->role !== 'admin' && $album->users_id !== $decode->id) {
            return response()->json([
                "status" => "error",
                "code" => 403,
                'message' => 'Akses ditolak',
            ], 403);
        }

        // Update data album
        $album->albums_title = $request->input('title');
        $album->albums_release_date = $request->input('release_date');
        $album->albums_genre = $request->input('genre');
        $album->albums_status = $request->input('status');

        // Cek apakah ada file cover yang diunggah
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverExtension = $cover->getClientOriginalExtension();
            $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
            $coverPath = 'albums/' . $coverName;
            $cover->move(public_path('albums'), $coverPath);
            $coverUrl = asset($coverPath);

            // Menghapus file cover lama jika ada
            if ($album->albums_cover) {
                $oldCoverPath = public_path('albums/' . basename($album->albums_cover));
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
            'data' => $album,
        ], 200);
    }


    public function delete_album($id)
    {
        $jwt = request()->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Album tidak ditemukan',
            ], 404);
        }

        // Memeriksa otorisasi
        if ($decode->role !== 'admin' && $album->users_id !== $decode->id) {
            return response()->json([
                "status" => "error",
                "code" => 403,
                'message' => 'Akses ditolak',
            ], 403);
        }

        // Menghapus file cover lama jika ada
        if ($album->albums_cover) {
            $oldCoverPath = public_path('albums/' . basename($album->albums_cover));
            if (file_exists($oldCoverPath)) {
                unlink($oldCoverPath);
            }
        }

        $album->delete();

        return response()->json([
            "data" => [
                "status" => "success",
                "code" => 200,
                "message" => "Album berhasil dihapus",
                "id" => $id
            ]
        ], 200);
    }
}
