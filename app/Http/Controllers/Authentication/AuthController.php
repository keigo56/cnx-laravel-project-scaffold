<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function validate_token(Request $request)
    {
        try {

            $user = auth()->user();

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
