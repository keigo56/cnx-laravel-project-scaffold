<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AzureSSOService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AzureSSOController extends Controller
{
    public function app_sso_login()
    {
        $azureService = new AzureSSOService;

        $authorizationURLAndClientState = $azureService->getAuthorizationURLAndClientState();
        $authorizationURL = $authorizationURLAndClientState['url'];
        $state = $authorizationURLAndClientState['state'];

        session(['oauthState' => $state]);

        return redirect()->away($authorizationURL);
    }

    public function app_sso_callback(Request $request)
    {

        try {

            $azureService = new AzureSSOService;
            $expectedState = session('oauthState');
            $request->session()->forget('oauthState');
            $providedState = $request->query('state');

            $azureService->isValidOAuthState($expectedState, $providedState);
            $authorizationCode = $request->query('code');
            $azureService->isValidAuthorizationCode($authorizationCode);
            $authenticatedUser = $azureService->getAzureSSOUser($authorizationCode);

            /*
             * Checks on the database if the user email exists on "users" table
             * */
            $userExists = User::query()
                ->where('email', '=', $authenticatedUser->getMail())
                ->exists();

            /*
             * If the user does not exist in the database,
             * create a new user record with the authenticated user's email and display name.
             * Otherwise, retrieve the existing user record from the database.
             * */

            $user = ! $userExists ?
                User::query()
                    ->create([
                        'email' => $authenticatedUser->getMail(),
                        'name' => $authenticatedUser->getDisplayName(),
                        'password' => Hash::make('password'),
                    ]) :
                User::query()
                    ->where('email', '=', $authenticatedUser->getMail())
                    ->first();

            /*
             * Delete all active tokens of the user
             * This is to ensure that only one session is active at a time
             * */
            $user->tokens()->delete();

            /*
             * Create the access token
             * Note that by default, this access token does not expire
             * We can update the expiration duration in the config settings
             * */
            $access_token = $user->createToken('access_token')->plainTextToken;

            /*
             * Forward the access token to our frontend URL
             * */

            $forwardURL = config('services.azure.frontend_uri')."/auth/validate?token={$access_token}";

            return redirect()->away($forwardURL);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function sso_logout(Request $request)
    {

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'User logout successfully',
        ]);
    }
}
