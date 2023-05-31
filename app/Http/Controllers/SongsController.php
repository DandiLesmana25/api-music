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

class SongsController extends Controller
{

    //*********************************** S O N G   M A N A G E M E N T *******************************//


    public function songs_index_id($id, Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(
            env('JWT_SECRET_KEY'),
            'HS256'
        )); //decode token

        $song = Song::find($id);


        if (!$song) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Lagu Tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $user = User::find($song->users_id);

        // Memeriksa status lagu
        if (($song->songs_status === 'pending' || $song->songs_status === 'unpublished') && $decode->role !== 'admin' && $song->users_id !== $decode->id_login
        ) {
            return response()->json([
                "status" => "error",
                "code" => 403,
                'message' => 'Akses ditolak',
                'data' => null,
            ], 403);
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
            'data' => [
                "id" => $song->id,
                "songs_title" => $song->songs_title,
                "songs_cover" => $song->songs_cover,
                "songs_song" => $song->songs_song,
                "songs_release_date" => $song->songs_release_date,
                "songs_status" => $song->songs_status,
                "users_id" => $song->users_id,
                "albums_id" => $song->albums_id,
                "songs_mood" => $song->songs_mood,
                "songs_genre" => $song->songs_genre,
                "created_at" => $song->created_at,
                "updated_at" => $song->updated_at,
                "artist_name" => $user->users_name
            ]
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
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Tidak ada lagu terbaru yang diputar',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => '5 lagu terbaru yang terakhir diputar oleh anda',
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

        $songs = Song::whereIn('id', $popularSongs)
            ->where('songs_status', 'published') // Menambahkan kondisi status lagu
            ->get();

        if ($songs->isEmpty()) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Lagu tidak ditemukan',
            ], 404);
        }

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => '5 Lagu terpopuler minggu ini',
            'data' => $songs,
        ], 200);
    }


    //*********************************** S O N G   M A N A G E M E N T *******************************//




    //********************************** M U S I C   M A N A G E M E N T *******************************//

    //Menambah Lagu
    public function add_song(Request $request)
    {
        $jwt = $request->bearerToken(); //ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decode token

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'cover' => 'required|mimes:png,jpg,jpeg|max:2048',
            'song' => 'required|file|mimes:mp3',
            'release_date' => 'nullable|date',
            'status' => 'nullable|in:Pending,Published,Unpublished',
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

        $file = $request->file('song');
        $laguExtension = $file->getClientOriginalExtension();
        $laguName = uniqid() . '_' . time() . '.' . $laguExtension;
        $laguPath = 'songs/' . $laguName;
        $file->move(public_path('songs'), $laguPath);
        $laguUrl = asset($laguPath);

        $cover = $request->file('cover');
        $coverExtension = $cover->getClientOriginalExtension();
        $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
        $coverPath = 'covers/' . $coverName;
        $cover->move(public_path('covers'), $coverPath);
        $coverUrl = asset($coverPath);

        $song = new Song();
        $song->songs_title = $request->title;
        $song->songs_cover = $coverUrl;
        $song->songs_song = $laguUrl;
        $song->songs_release_date = $request->release_date ?? now();
        $song->songs_status = $request->status ?? 'pending'; // Menggunakan nilai default 'pending' jika status tidak disertakan dalam request
        $song->users_id = $decode->id_login;
        $song->albums_id = $request->id_album;
        $song->songs_mood = $request->mood;
        $song->songs_genre = $request->genre;
        $song->save();

        return response()->json(
            [
                "status" => "success",
                'code' => 200,
                'message' => 'Lagu berhasil diunggah',
                'data' => $song
            ],
            200
        );
    }


    public function songs_index()
    {
        $jwt = request()->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));

        $user = User::find($decode->id_login);

        if (!$user) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Pengguna tidak ditemukan',
            ], 404);
        }

        if ($user->users_role === 'admin') {
            $songs = Song::all();
        } else {
            $songs = Song::where('users_id', $decode->id_login)
                ->where('songs_status', 'published')
                ->orWhere(function ($query) use ($decode) {
                    $query->where('users_id', $decode->id_login)
                        ->where('songs_status', 'pending');
                })
                ->get();
        }

        return response()->json([
            "status" => "success",
            "code" => 200,
            'message' => 'Berhasil menampilkan daftar lagu',
            'data' => $songs,
        ], 200);
    }



    //Edit lagu
    public function edit_song(Request $request, $id)
    {
        $jwt = $request->bearerToken(); // Ambil token
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); // Decode token

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'cover' => 'required|mimes:png,jpg,jpeg|max:2048',
            'song' => 'required|file|mimes:mp3',
            'release_date' => 'required|date',
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


        // Update data lagu
        $song->songs_title = $request->title;
        $song->songs_release_date = $request->release_date;
        $song->songs_status = $request->status ?? 'pending'; // Menggunakan nilai default 'pending' jika status tidak disertakan dalam request
        $song->users_id = $decode->id_login;
        $song->albums_id = $request->id_album;
        $song->songs_mood = $request->mood;
        $song->songs_genre = $request->genre;

        // Cek apakah ada file cover yang diunggah
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverExtension = $cover->getClientOriginalExtension();
            $coverName = uniqid() . '_' . time() . '.' . $coverExtension;
            $coverPath = 'covers/' . $coverName;
            $cover->move(public_path('covers'), $coverPath);
            $coverUrl = asset($coverPath);

            // Menghapus file cover lama jika ada
            if ($song->songs_cover) {
                $oldCoverPath = public_path('covers/' . basename($song->songs_cover));
                if (file_exists($oldCoverPath)) {
                    unlink($oldCoverPath);
                }
            }

            $song->songs_cover = $coverUrl;
        }

        if ($request->hasFile('lagu')) {
            $file = $request->file('lagu');
            $laguExtension = $file->getClientOriginalExtension();
            $laguName = uniqid() . '_' . time() . '.' . $laguExtension;
            $laguPath = 'songs/' . $laguName;
            $file->move(public_path('songs'), $laguPath);
            $laguUrl = asset($laguPath);

            // Menghapus file lagu lama jika ada
            if ($song->songs_song) {
                $oldCoverPath = public_path('songs/' . basename($song->songs_song));
                if (file_exists($oldCoverPath)) {
                    unlink($oldCoverPath);
                }
            }

            $song->songs_song = $laguUrl;
        }

        $song->save();

        return response()->json([
            'message' => 'Lagu berhasil diubah',
            'status' => 200,
            'data' => $song
        ], 200);
    }

    public function delete_song($id)
    {
        $song = Song::find($id);

        if (!$song) {
            return response()->json([
                "status" => "error",
                "code" => 404,
                'message' => 'Lagu tidak ditemukan',
            ], 404);
        }

        // Menghapus entri yang terkait dalam tabel view_song
        DB::table('view_song')->where('songs_id', $id)->delete();

        $song->delete();

        $oldCoverPath = public_path('songs/' . basename($song->songs_song));
        if (file_exists($oldCoverPath)) {
            unlink($oldCoverPath);
        }

        $oldCoverPath = public_path('covers/' . basename($song->songs_cover));
        if (file_exists($oldCoverPath)) {
            unlink($oldCoverPath);
        }


        return response()->json([
            "status" => "success",
            "code" => 200,
            "message" => "Lagu berhasil dihapus",
            "data" => $song
        ], 200);
    }


    //********************************** M U S I C   M A N A  G E M E N T *******************************//

    public function pending_song()
    {
        $songs = Song::where('songs_status', 'Pending')->get();

        if ($songs->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Lagu dengan status "pending" tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Lagu dengan status "pending"',
            'data' => $songs,
        ]);
    }


    public function publish_song($id)
    {
        $song = Song::find($id);

        if (!$song) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Lagu tidak ditemukan',
            ], 404);
        }

        $song->songs_status = 'published';
        $song->save();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Lagu berhasil diubah menjadi "published"',
            'data' => $song,
        ]);
    }

    public function unpublish_song($id)
    {
        $song = Song::find($id);

        if (!$song) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Lagu tidak ditemukan',
            ], 404);
        }

        $song->songs_status = 'unpublished';
        $song->save();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Lagu berhasil diubah menjadi "unpublished"',
            'data' => $song,
        ]);
    }
}
