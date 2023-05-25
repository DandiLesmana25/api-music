<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// panggil library jwt 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class CreatorMiddleware
{


    public function handle(Request $request, Closure $next)
    {
        try {
            $jwt = $request->bearerToken(); //ambil token
            $decode = JWT::decode($jwt, new Key(env('JWT_SECRET_KEY'), 'HS256')); //decoce token

            // kondisi jika role pada token adalah admin, maka lanjut proses selanjutnya
            if ($decode->role == 'admin') {
                return $next($request);
            } else {
                // jika bukan role admin
                return response()->json("Unauthorized", 401);
            }
        } catch (ExpiredException $e) {
            // jika token expired
            return response()->json($e->getMessage(), 400);
        }
    }
}
