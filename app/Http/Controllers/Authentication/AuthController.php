<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function validate_token(Request $request)
    {
        try {

            $user = auth()->user();

            // Invalidate existing sessions for the same user
            // This is to make sure that one user is authenticated at a time
            DB::table('sessions')->where('user_id', $user->id)->delete();

            Auth::guard('web')->login($user);

            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'User token is valid'
            ]);

        }catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }
}
