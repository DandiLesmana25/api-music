<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PlaylistController extends Controller
{
    public function create_playlist(Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decoce token

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:private,public',
        ]);

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        $gambarPath = $request->file('gambar');

        // ubah nama file yang akan dimasukan ke server
        $fileimg= now()->timestamp."_".$request->gambar->getClientOriginalName();
        $gambarPath->move('uploads', $fileimg); 

        $playlist = Playlist::create([
            'nama' => $request->input('nama'),
            'gambar' => $fileimg,
            'status' => $request->input('status'),
            'id_user' => $decode->id_login,
        ]);

        return response()->json([
            'message' => 'Playlist created',
            'data' => $playlist,
        ]);
    }

    public function show_all_playlist() {
        // Mengambil semua daftar putar dari model Playlist
        $playlists = Playlist::all();
        
        // Memeriksa apakah ada daftar putar yang ditemukan
        if ($playlists->isNotEmpty()) {
            // Mengubah data daftar putar menjadi array
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

    public function delete_playlist($id){
        $playlist = Playlist::find($id);

        if($playlist) {
            $playlist->delete();

            return response()->json([
                "data" => [
                    'message' => 'playlist dengan id '.$id.', berhasil dihapus'
                ]
            ],200);
        }

        return response()->json([
            "data" => [
                'message' => 'playlist id: '.$id.', tidak ditemukan'
            ]
            ],422);
    }

public function update_playlist(Request $request, $id) {
    // Mengambil data daftar putar yang akan diperbarui
        $playlist = Playlist::find($id);
    
    if ($playlist) {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required | string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:private,public',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->messages()->first(),
            ]);
        }
    
        // Perbarui data daftar putar
        $playlist->nama = $request->input('nama');
        $playlist->status = $request->input('status');
    
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar');
            // Ubah nama file yang akan dimasukkan ke server
            $fileimg = now()->timestamp . "_" . $request->gambar->getClientOriginalName();
            $gambarPath->move('uploads', $fileimg);
            $playlist->gambar = $fileimg;
        }
    
        $playlist->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Daftar putar berhasil diperbarui',
            'data' => $playlist,
        ]);
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Daftar putar tidak ditemukan',
        ]);
    }
}


}
