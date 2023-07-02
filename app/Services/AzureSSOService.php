<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\User;

class AzureSSOService
{
    /*
     * Returns the Azure OAuth Client which can be used to connect to Azure AD API
     * */
    private function getOAuthClient(): GenericProvider
    {
       return new GenericProvider([
            'clientId'                => config('services.azure.app_id'),
            'clientSecret'            => config('services.azure.secret'),
            'redirectUri'             => config('services.azure.redirect_uri'),
            'urlAuthorize'            => config('services.azure.authorize_endpoint'),
            'urlAccessToken'          => config('services.azure.token_endpoint'),
            'scopes'                  => config('services.azure.scopes'),
            'urlResourceOwnerDetails' => '',
        ]);
    }

    public function getAuthorizationURLAndClientState(): array
    {
        /*
         * Initialize the Oauth Client to use Azure AD API
         * */
        $client = $this->getOAuthClient();

        /*
         * Generate the Authorization URL where users will sign in
         * Generate the Client State for verification purposes
         *
         * Options [prompt => consent]:
         * This ensures that users will have to accept the permissions requested once sign in is successful
         *
         * */
        return [
            'url' => $client->getAuthorizationUrl(['prompt' => 'consent']),
            'state' => $client->getState()
        ];
    }

    /*
     * Returns if the OAuth State is valid
     * */

    /**
     * @throws Exception
     */
    public function isValidOAuthState(string|null $expectedState, string|null $providedState): bool
    {
        if (!isset($expectedState)) {
            throw new Exception('Invalid OAuth State');
        }

        if (!isset($providedState)) {
            throw new Exception('Invalid OAuth State');
        }

        if ($expectedState !== $providedState) {
            throw new Exception('Invalid OAuth State');
        }

        return true;
    }

    /*
     * Returns if the OAuth Code is valid
     * */
    /**
     * @throws Exception
     */
    public function isValidAuthorizationCode(string $authorizationCode): bool
    {
        if (!isset($authorizationCode)) {
            throw new Exception('Invalid Authorization Code');
        }

        return true;
    }

    /*
    * Returns the access token to be used in querying the Microsoft Graph API
    * */
    /**
     * @throws Exception
     */
    public function getAccessToken(string $authorizationCode): AccessToken|bool
    {
        /*
        * Initialize the Oauth Client to use Azure AD API
        * */
        $client = $this->getOAuthClient();

        try {
            return $client->getAccessToken('authorization_code', [
                'code' => $authorizationCode
            ]);
        }catch (IdentityProviderException $e) {
            throw new Exception('Cannot get access token for OAuth Client');
        }
    }

    /*
    * Returns the authenticated user from Azure AD
    * */
    /**
     * @throws Exception
     */
    public function getAzureSSOUser(AccessToken $token)
    {
        /*
         * Initializes the Microsoft Graph API
         * Sets the access token for the Graph API
         * */
        $graph = new Graph();
        $graph->setAccessToken($token->getToken());

        try {
            return $graph
                ->createRequest('GET', '/me')
                ->setReturnType(User::class)
                ->execute();
        } catch (GuzzleException|GraphException $e) {
            throw new Exception('Cannot get Azure SSO User');
        }
    }
}
