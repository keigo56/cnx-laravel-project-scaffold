<?php

namespace App\Http\Controllers\Authentication;

use App\Auth\Handlers\BasicAuthHandler;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserBasicAuthController extends Controller
{
    /*
     * The BasicAuthService class is responsible for handling the Basic authentication process
     *
     * It is used to authenticate the user with email and password
     * */
    protected BasicAuthHandler $authHandler;

    public function __construct(BasicAuthHandler $authHandler)
    {
        $this->authHandler = $authHandler;
        $this->authHandler->withRole('user');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {

            $email = $request->input('email');
            $password = $request->input('password'); // Password is encrypted by default

            $result = $this
                ->authHandler
                ->withCredentials($email, $password)
                ->authenticate();

            return response()->json([
                'success' => true,
                ...$result,
            ]);

        } catch (ValidationException $validationException) {
            throw $validationException;
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to login. Something went wrong. '.$exception->getMessage(),
            ], 500);
        }
    }

    public function refreshOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'login_authorization_id' => ['required'],
        ]);

        try {

            $email = $request->input('email');
            $login_authorization_id = $request->input('login_authorization_id');

            $this->authHandler
                ->withEmail($email)
                ->withLoginAuthorizationId($login_authorization_id)
                ->refreshOTP();

            return response()->json([
                'success' => true,
                'email' => $email,
                'message' => 'OTP sent to email successfully',
            ]);

        } catch (ValidationException $validationException) {
            throw $validationException;
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to generate OTP. '.$exception->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
            'login_authorization_id' => ['required'],
            'otp' => ['required'],
        ]);

        try {

            $email = $request->input('email');
            $loginAuthorizationID = $request->input('login_authorization_id');
            $otpCode = $request->input('otp');

            $accessToken = $this
                ->authHandler
                ->withMFACredentials($email, $loginAuthorizationID, $otpCode)
                ->verifyOtp()
                ->finalizeAuthentication();

            return response()->json([
                'success' => true,
                'email' => $email,
                'access_token' => $accessToken,
                'message' => 'OTP provided is valid.',
            ]);

        } catch (ValidationException $validationException) {
            throw $validationException;
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify OTP. '.$exception->getMessage(),
            ], 500);
        }
    }

    public function validateToken(Request $request)
    {
        try {

            $user = Auth::user();

            // Invalidate existing sessions for the same user
            // This is to make sure that one user is authenticated at a time
            DB::table('sessions')->where('user_id', $user->id)->delete();

            Auth::guard('web')->login($user);

            $request->session()->regenerate();

            activity()->log('User logged in as user');

            return response()->json([
                'success' => true,
                'message' => 'User token is valid',
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {

            $user = User::findOrFail(Auth::user()->id);

            activity()->log('User logged out as user');

            $user->tokens()->delete();

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'User logout successful',
            ]);

        } catch (Exception) {
            return response()->json([
                'error' => true,
                'message' => 'Logout failed. Please try again later.',
            ], 500);
        }
    }
}
