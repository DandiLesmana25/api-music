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

class SearchController extends Controller
{

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $jwt = $request->bearerToken();
        $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256'));
        $userId = $decode->id_login;

        $songs = Song::where('songs_title', 'LIKE', '%' . $keyword . '%')
            ->join('users', 'songs.users_id', '=', 'users.id')
            ->where(function ($query) use ($userId) {
                $query->where('songs.songs_status', '=', 'published')
                    ->orWhere(function ($query) use ($userId) {
                        $query->where('songs.songs_status', '=', 'pending')
                            ->where('songs.users_id', '=', $userId);
                    });
            })
            ->select('songs.*', 'users.users_name')
            ->get();

        return $songs;
    }
}
