<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;
use App\Models\User_Deleted;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{


    //------------------------------------------------------------------------------//
    //Registrasi akun via admin
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirmation_password' => 'required|same:password',
            'role' => 'required|in:admin,user,creator',
        ]);

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }
        $user = $validator->validated();

        User::create($user);

        return response()->json([
            "data" => [
                'msg' => "Akun berhasil di buat",
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 200);
    }
    //------------------------------------------------------------------------------//


    //------------------------------------------------------------------------------//

    public function show_register()
    {
        // Mencari semua akun dengan role user atau creator
        $users = User::whereIn('role', ['user', 'creator'])->get();

        return response()->json([
            "data" => [
                'msg' => "User registration",
                'data' => $users
            ]
        ], 200);
    }

    //------------------------------------------------------------------------------//



    //------------------------------------------------------------------------------//

    public function show_register_by_id($id)
    {

        // munculkan akun berdasarkan id
        $user = User::find($id);

        return response()->json([
            "data" => [
                'msg' => "user id:{$id}",
                'data' => $user
            ]
        ], 200);
    }

    //------------------------------------------------------------------------------//




    //------------------------------------------------------------------------------//

    public function update_register(Request $request, $id)
    {
        $user = User::find($id);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'password' => 'min:8',
                'confirmation_password' => 'same:password',
                'email' => 'email',
                'role' => 'required|in:admin,user,creator',
            ]);

            if ($validator->fails()) {
                return messageError($validator->messages()->toArray());
            }

            $data = $request->only(['name', 'password', 'email', 'role']);

            User::where('id', $id)->update($data);

            return response()->json([
                'data' => [
                    "msg" => 'user dengan id : ' . $id . ' berhasil diupdate',
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                ]
            ], 200);
        }

        return response()->json([
            "data" => [
                'msg' => 'user dengan id: ' . $id . ' tidak ditemukan'
            ]
        ], 422);
    }


    //------------------------------------------------------------------------------//





    //------------------------------------------------------------------------------//

    public function delete_register($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        User_Deleted::create([
            'name' => $user->name,
            'email' => $user->email,
            'deleted_by' => '1' // masih belum temu cara
        ]);

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }

    //------------------------------------------------------------------------------//


}
