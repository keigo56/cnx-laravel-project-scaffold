<?php

namespace App\Auth\Services;

use Exception;
use League\OAuth2\Client\Provider\GenericProvider;
use Microsoft\Graph\Generated\Models\User;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Authentication\Oauth\AuthorizationCodeContext;

class AzureSSOService
{
    /*
     * The OAuth Client to be used in querying the Microsoft Graph API
     * */
    protected GenericProvider $client;

    /*
     * The state to be used in validating the OAuth State
     * */
    protected string $state;

    /*
     * The expected state to be used in validating the OAuth State
     * */
    protected string $expectedState;

    /*
     * The state returned by the Azure SSO authorization URL
     * */
    protected string $providedState;

    /*
     * The authorization code returned by the Azure SSO authorization URL
     * */
    protected string $authCode;

    public function __construct()
    {
        // Initialize the Oauth Client to redirect to Azure Login Page
        $this->client = $this->getOAuthClient();
    }

    /*
     * Returns the URL to redirect the user to for authorization
     *
     */
    public function getAuthorizationRedirectUrl(): string
    {
        $params = [
            'prompt' => 'consent', // This ensures that users will have to accept the permissions requested once sign-in was successful
        ];

        if (isset($this->state)) {
            $params['state'] = $this->state;
        }

        return $this->client->getAuthorizationUrl($params);
    }

    /*
     * Returns the OAuth Client to be used in querying the Microsoft Graph API
     * */
    private function getOAuthClient(): GenericProvider
    {
        return new GenericProvider([
            'tenantId' => config('services.azure.tenant_id'),
            'clientId' => config('services.azure.app_id'),
            'clientSecret' => config('services.azure.secret'),
            'redirectUri' => config('services.azure.redirect_uri'),
            'urlAuthorize' => config('services.azure.authorize_endpoint'),
            'urlAccessToken' => config('services.azure.token_endpoint'),
            'scopes' => config('services.azure.scopes'),
            'urlResourceOwnerDetails' => '',
        ]);
    }

    /*
     * Sets the state to be used in validating the OAuth State
     * */
    public function withState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /*
     * Sets the expected state to be used in validating the OAuth State
     * */
    public function withExpectedState(string $state): self
    {
        $this->expectedState = $state;

        return $this;
    }

    /*
     * Sets the state returned by the Azure SSO authorization URL
     * */
    public function withReturnedState(string $state): self
    {
        $this->providedState = $state;

        return $this;
    }

    /*
     * Sets the authorization code returned by the Azure SSO authorization URL
     * */
    public function withAuthorizationCode(string $code): self
    {
        $this->authCode = $code;

        return $this;
    }

    protected function validateState(): void
    {
        if (! hash_equals($this->expectedState, $this->providedState)) {
            throw new Exception('OAuth state mismatch.');
        }
    }

    /**
     * Returns the authenticated user from Azure AD
     *
     * @throws Exception
     */
    public function getUser(): User
    {
        try {

            $this->validateState();

            $tokenRequestContext = new AuthorizationCodeContext(
                config('services.azure.tenant_id'),
                config('services.azure.app_id'),
                config('services.azure.secret'),
                $this->authCode,
                config('services.azure.redirect_uri')
            );

            $scopes = array_map('trim', explode(',', config('services.azure.scopes')));
            $graphServiceClient = new GraphServiceClient($tokenRequestContext, $scopes);

            return $graphServiceClient
                ->me()
                ->get()
                ->wait();

        } catch (Exception $e) {
            throw new Exception('Cannot get Azure SSO User: '.$e->getMessage());
        }
    }
}
