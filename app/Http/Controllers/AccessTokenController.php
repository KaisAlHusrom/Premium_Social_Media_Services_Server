<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokenController extends Controller
{
    public function getAccessToken(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::user();

        if ($user) {
            return response()->json(['error' => 'No1 valid access token found'], 401);
            // Get the user's access tokens
            $lastValidatedToken = PersonalAccessToken::where('tokenable_id', $user->id)
                ->where('tokenable_type', get_class($user))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastValidatedToken && !$lastValidatedToken->isExpired() && !$lastValidatedToken->revoked) {
                return response()->json(['access_token' => $lastValidatedToken->plainTextToken]);
            }
        }

        // If the user is not authenticated or there's no valid token, return an error response
        return response()->json(['error' => 'No valid access token found'], 401);
    }
}
