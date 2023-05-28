<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\User;
use App\Models\Song;
use App\Models\Album;


class AlbumsController extends Controller
{


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
        $coverPath = 'albums/' . $coverName;
        $cover->move(public_path('albums'), $coverPath);

        $album = new Album();
        $album->albums_title = $request->input('judul');
        $album->albums_artist = $decode->name;
        $album->albums_cover = url($coverPath);
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
        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Album tidak ditemukan',
            ], 404);
        }


        $album->delete();

        // Menghapus file cover lama jika ada
        if ($album->albums_cover) {
            $oldCoverPath = public_path('albums/' . basename($album->albums_cover));
            if (file_exists($oldCoverPath)) {
                unlink($oldCoverPath);
            }
        }

        return response()->json([
            "data" => [
                "message" => "Album berhasil dihapus",
                "id" => $id
            ]
        ], 200);
    }
}
