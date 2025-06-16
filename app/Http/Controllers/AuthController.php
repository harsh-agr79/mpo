<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
        public function login(Request $request)
    {
        $request->validate([
            'userid' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('userid', $request->userid)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // Create token
        $token = $user->createToken('auth_token', ['*'], now()->addDay())->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}
