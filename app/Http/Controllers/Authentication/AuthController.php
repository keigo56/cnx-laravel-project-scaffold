<?php

namespace App\Http\Controllers\Authentication;

use App\Auth\AuthManager;
use App\Auth\Handlers\BasicAuthHandler;
use App\Auth\Services\OTPService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /*
     * The AuthManager class is responsible for handling the authentication process
     *
     * It is used to check if the role is configured for SSO authentication
     * and to get the redirect path on the SPA frontend for the role after authentication
     * */
    protected AuthManager $authManager;

    /*
     * The BasicAuthService class is responsible for handling the Basic authentication process
     *
     * It is used to authenticate the user with email and password
     * */
    protected BasicAuthHandler $authHandler;

    /*
     * The OTPService class is responsible for handling the OTP process
     *
     * It is used to send the OTP to the user and verify the OTP
     * */
    protected OTPService $otpService;

    public function __construct(AuthManager $authManager, BasicAuthHandler $authHandler)
    {
        $this->authManager = $authManager;
        $this->authHandler = $authHandler;
    }

    public function login(string $role, Request $request)
    {
        /*
         * Check if the role is configured for Basic authentication
         * If not, throw an exception
         * */
        $this->authManager->checkIfAuthFlowExistsForRole('basic', $role);

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
                ->withRole($role)
                ->authenticate();

            return response()->json($result);

        } catch (ValidationException $validationException) {
            throw $validationException;
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to login. Something went wrong. '.$exception->getMessage(),
            ], 500);
        }
    }

    public function refresh_otp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'login_authorization_id' => ['required'],
        ]);

        try {

            $email = $request->input('email');
            $login_authorization_id = $request->input('login_authorization_id');

            $newUserOTP = $this->otpService->refreshOTP($email, $login_authorization_id);
            $this->otpService->sendOTPToMail($newUserOTP->user_email, $newUserOTP->otp);

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

    public function verify_otp(string $role, Request $request)
    {

        $request->validate([
            'email' => ['required', 'email'],
            'login_authorization_id' => ['required'],
            'otp' => ['required'],
        ]);

        try {

            $email = $request->input('email');
            $login_authorization_id = $request->input('login_authorization_id');
            $otp = $request->input('otp');

            $this->otpService->verifyOTP($email, $login_authorization_id, $otp);

            $result = $this
                ->authHandler
                ->withEmail($email)
                ->withRole($role)
                ->provideLoginAccess();

            return response()->json($result);

        } catch (ValidationException $validationException) {
            throw $validationException;
        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify OTP. '.$exception->getMessage(),
            ], 500);
        }
    }

    public function validate_token(string $role, Request $request)
    {
        try {

            $user = Auth::user();

            // Invalidate existing sessions for the same user
            // This is to make sure that one user is authenticated at a time
            DB::table('sessions')->where('user_id', $user->id)->delete();

            Auth::guard('web')->login($user);

            $request->session()->regenerate();

            $roleName = $this->authManager->getRoleName($role);
            activity()->log("User logged in as {$roleName}");

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

    public function logout(string $role, Request $request)
    {
        try {

            $user = User::findOrFail(Auth::user()->id);

            $roleName = $this->authManager->getRoleName($role);
            activity()->log("User logged out as {$roleName}");

            $user->tokens()->delete();

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => $roleName.' logout successful',
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'error' => true,
                'message' => 'Logout failed. Please try again later.',
            ], 500);
        }
    }
}
