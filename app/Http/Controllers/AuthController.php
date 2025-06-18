<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


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

    public function logout( Request $request ) {
        $user = $request->user();

        if ( $user ) {
            // Revoke all tokens for the authenticated user
            $user->tokens()->delete();

            return response()->json( [ 'message' => 'Logged out successfully' ], 200 )
            ->cookie( 'auth_token', '', -1 );
            // Remove the auth_token cookie
        }

        return response()->json( [ 'error' => 'Not authenticated' ], 401 );
    }

    public function checkToken( Request $request ) {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ( !$token ) {
            return response()->json( [ 'error' => 'No token found.' ], 401 );
        }

        // Check if token is older than 30 days
        if ( $token->expires_at->lt( Carbon::now() ) ) {
            $token->delete();
            // Delete expired token
            return response()->json( [ 'error' => 'Token expired.' ], 401 );
        }

        return response()->json( [ 'message' => 'Token is valid.' ], 200 );
    }
}
