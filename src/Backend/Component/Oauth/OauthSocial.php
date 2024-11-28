<?php

namespace App\Backend\Component\Oauth;

use App\Backend\Model\User\Provider;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\AbstractProvider;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Session\SessionInterface;

final class OauthSocial
{
    private AbstractProvider $provider;

    public function __construct(
        private Provider $socialProvider,
        private ConfigInterface $config,
        private SessionInterface $session
    ) {
        $params = $config->get('params');

        switch ($this->socialProvider) {
            case Provider::Google:
                $this->provider = new Google(
                    [
                        'clientId' => $params['oauth']['google']['clientId'],
                        'clientSecret' => $params['oauth']['google']['clientSecret'],
                        'redirectUri' => $params['oauth']['google']['redirectUri']
                    ]
                );
                break;

            case Provider::Facebook:
                $this->provider = new Facebook(
                    [
                        'clientId' => $params['oauth']['facebook']['clientId'],
                        'clientSecret' => $params['oauth']['facebook']['clientSecret'],
                        'redirectUri' => $params['oauth']['facebook']['redirectUri'],
                        'graphApiVersion' => "v15.0"
                    ]
                );
                break;
        }
    }


    public function getAuthorizationUrl(): string
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        $this->session->set("{$this->socialProvider->value}OauthState", $this->provider->getState());

        return $authorizationUrl;
    }


    public function getAuthorizationData(string $code): object
    {
        $accessToken = $this->provider->getAccessToken("authorization_code", compact("code"));
        $token = $accessToken->getToken();
        $tokenExpiresTimestamp = $accessToken->getExpires();
        $tokenExpiresDate = date('Y-m-d H:i:s', $tokenExpiresTimestamp);
        $refreshToken = $accessToken->getRefreshToken();

        $authInfo = $this->provider->getResourceOwner($accessToken);

        $identifier = $authInfo->getId();
        $email = $authInfo->getEmail();
        $firstName = $authInfo->getFirstName();
        $lastName = $authInfo->getLastName();

        $authInfo = array_merge(
            compact("token", "tokenExpiresTimestamp", "tokenExpiresDate", "refreshToken", "identifier", "email", "firstName", "lastName")
        );

        return (object)$authInfo;
    }
}
