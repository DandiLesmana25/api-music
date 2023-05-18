<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function update_password(Request $request, $id)
    {
        //
        $user = User::find($id);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'min:8',
                'confirmation_password' => 'same:password',
                'email' => 'email',
                'role' => 'required|in:admin,user,contributor',
            ]);

            if ($validator->fails()) {
                return messageError($validator->messages()->toArray());
            }

            $data = $validator->validated();

            User::where('id', $id)->update($data);

            return response()->json([
                'data' => [
                    "msg" => 'user dangan id : ' . $id . ' berhasil di update',
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'user id : ' . $id . ', tidak ditemukan'
            ]
        ], 422);
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
