<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index()
    {
        //
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        //
    }


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


    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
