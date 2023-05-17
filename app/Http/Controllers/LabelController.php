<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $labels = Label::all();

        return response()->json([
            'message' => 'Success',
            'data' => $labels,
        ]);
        
    }

  
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //

        // $validator = Validator::make($request->all(), [
            
                
        $request->validate([
            'label' => 'required',
            'cover' => 'required',
            'kategori' => 'required|in:album,single',
            'tanggal_rilis' => 'required|date',
            'id_user' => 'required|exists:users,id',
        ]);

        $label = Label::create([
            'label' => $request->label,
            'cover' => $request->cover,
            'kategori' => $request->kategori,
            'tanggal_rilis' => $request->tanggal_rilis,
            'id_user' => $request->id_user,
        ]);

        return response()->json([
            'message' => 'Data label berhasil disimpan',
            'data' => $label,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $label = Label::findOrFail($id);

        return response()->json([
            'message' => 'Success',
            'data' => $label,
        ]);

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
