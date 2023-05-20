<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Music;


class MusicController extends Controller
{

    public function add_music(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'cover' => 'nullable|image',
            'lagu' => 'required|file|mimes:mp3',
            'tanggal_rilis' => 'required|date',
            'status' => 'required|in:pending,published,unpublished',
            'id_user' => 'required|exists:users,id',
            'id_label' => 'nullable|exists:labels,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'status' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }


        $lagu = $request->file('lagu');
        $laguPath = 'lagu/' . date("y-m-d-h-i-s") . "_" . $lagu->getClientOriginalName();
        $lagu->storeAs('public/lagu', $laguPath);

        $cover = $request->file('cover');
        $coverPath = 'cover/' . date("y-m-d-h-i-s") . "_" . $cover->getClientOriginalName();
        $cover->storeAs('public/cover', $coverPath);

        $song = Music::create([
            'judul' => $request->judul,
            'cover' => $request->coverPath,
            'lagu' => $laguPath,
            'tanggal_rilis' => $request->tanggal_rilis,
            'status' => $request->status,
            'id_user' => $request->id_user,
            'id_label' => $request->id_label,
        ]);

        return response()->json([
            'message' => 'Song created successfully',
            'status' => 200,
            'data' => $song,
        ], 200);
    }
}
