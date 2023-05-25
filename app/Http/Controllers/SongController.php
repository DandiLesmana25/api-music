<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\User;
use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class SongController extends Controller
{

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
            return response()->json([
                'message' => 'Invalid data',
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
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


    public function songs_index_id($id)
    {

        $song = Song::find($id);

        if (!$song) {
            return response()->json([
                'message' => 'Lagu Tidak di Temukan',
                'statusCode' => 404,
            ], 404);
        }

        return response()->json([
            'message' => 'Lagu dengan id : ' . $id,
            'statusCode' => 200,
            'data' => $song,
        ], 200);
    }


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
            return response()->json([
                'message' => 'Invalid data',
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
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
                Storage::delete(parse_url($song->cover, PHP_URL_PATH));
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


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
