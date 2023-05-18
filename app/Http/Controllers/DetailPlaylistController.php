<?php

namespace App\Http\Controllers;

use App\Models\DetailPlaylist;
use Illuminate\Http\Request;

class DetailPlaylistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $detailPlaylists = DetailPlaylist::all();
        return response()->json([
            'data' => $detailPlaylists,
        ], 200);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DetailPlaylist  $detailPlaylist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        // return response()->json([
        //     'data' => $detailPlaylist,
        // ], 200);


        $detailPlaylist = DetailPlaylist::find($id);

        if (!$detailPlaylist) {
            return response()->json(['message' => 'Detail playlist not found'], 404);
        }

        return response()->json($detailPlaylist);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DetailPlaylist  $detailPlaylist
     * @return \Illuminate\Http\Response
     */
    public function edit(DetailPlaylist $detailPlaylist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DetailPlaylist  $detailPlaylist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DetailPlaylist $detailPlaylist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DetailPlaylist  $detailPlaylist
     * @return \Illuminate\Http\Response
     */
    public function destroy(DetailPlaylist $detailPlaylist)
    {
        //
    }
}
