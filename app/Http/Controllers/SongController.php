<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\User;
use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Validator;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

        $songs = Song::all();
        // $song = Song::find();

        return response()->json([
            'message' => 'Berhasil menampilkan daftar lagu',
            'statusCode' => 200,
            'data' => $songs,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    $validator = Validator::make($request->all(), [
        'judul' => 'required|string',
        'cover' => 'required|string',
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

    // code agar lagu kesimpen di storage/app/public/lagu
    // $lagu = $request->file('lagu');
    // $laguPath = $lagu->store('lagu', 'public');
    
    
    // dari a ray(agar lagunya kesimpen di public)
    // $file = $request->file;
    // $filename_foto = date("y-m-d-h-i-s") . "_" . $file->getClientOriginalName();
    // $request->file->move('song/', $filename_foto);

    //  lagunya sudah tersimpan di file public
    $file = $request->file('lagu');
    $laguPath = 'lagu/' . date("y-m-d-h-i-s") . "_" . $file->getClientOriginalName();
    $file->move(public_path('lagu'), $laguPath);

    $song = new Song();
    $song->judul = $request->judul;
    $song->cover = $request->cover;
    $song->lagu = $laguPath;
    // $song->lagu = $filename_foto;
    $song->tanggal_rilis = $request->tanggal_rilis;
    $song->status = $request->status;
    $song->id_user = $request->id_user;
    $song->id_label = $request->id_label;
    $song->save();

    // $song = Song::create([
    //     'judul' => $data['judul'],
    //     'cover' => $data['cover'],
    //     'lagu' => $laguPath,
    //     'tanggal_rilis' => $data['tanggal_rilis'],
    //     'status' => $data['status'],
    //     'id_user' => $data['id_user'],
    //     'id_label' => $data['id_label'],
    // ]);

    return response()->json([
        'message' => 'Song created successfully',
        'status' => 200,
        'data' => $song,
    ], 200);
        
    }

    // public function add_song(Request $request)
    // {
    //     $data = $request->all();
    //     $v = Validator::make($data, [
    //         'judul' => 'required|string',
    //         'cover' => 'required|string',
    //         'lagu' => 'required|mimes:mpga,wav,mp3',
    //         'tanggal_rilis' => 'required|date',
    //         'status' => 'in:pending,published,rejected|default:pending',
    //         'id_user' => 'required|integer',
    //         'id_label' => 'required|integer',
    //     ]);

    //     // if ($v->fails()) {
    //     //     return response()->json([
    //     //         'message' => 'Invalid data',
    //     //         'statusCode' => 400,
    //     //     ], 400);
    //     // }

    //     $user = User::find($data['id_user']);
    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'user not found',
    //             'statusCode' => 404,
    //         ], 404);
    //     }

    //     $label = $data['id_label'] ? Label::find($data['id_label']) : null;
    //     if (isset($data['id_label']) && !$label) {
    //         return response()->json([
    //             'message' => 'Label not found',
    //             'statusCode' => 404,
    //         ], 404);
    //     }

      
    //     $song = $request->file('song');

    //     // $lagu = time() . '_' . $lagu->getClientOriginalName();
    //     // $fileName = time() . '_' . $lagu->getClientOriginalName();
    //     $song->move(public_path('song'), $song);


    //     // $file = $request->file;
        
    //     // $song = $file->getClientOriginalName();
    //     // $file->move('songs/' . $user->id, $song);

    //     try {
    //         $data = $v->validated();

    //         $song = Song::create([
    //             'judul' => $data['judul'],
    //             'lagu' => $lagu,
    //             'tanggal_rilis' => $data['tanggal_rilis'],
    //             'status' => $data['status'] ?? 'pending',
    //             'id_user' => $data['id_user'],
    //             'id_label' => $data['id_label'] ?? null,
    //         ]);

    //         $song['song'] ? $song['song'] = url('song/' . $song['song']) : null;

    //         return response()->json([
    //             'message' => 'Create song successful',
    //             'statusCode' => 200,
    //             'data' => $song,
    //         ], 200);
    //     } catch (Exception $e) {
    //         if (file_exists(public_path('audio/' . $audio_name))) unlink(public_path('audio/' . $audio_name));

    //         return response()->json([
    //             'message' => 'Create song failed',
    //             'statusCode' => 500,
    //         ], 500);
    //     }
    // }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
       

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
