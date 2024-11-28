<?php

namespace App\Backend\Component\Gmailer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Mailer\Mailer as BaseMailer;
use Yiisoft\Mailer\MessageInterface;
use Yiisoft\Mailer\MessageBodyRenderer;
use Yiisoft\Mailer\MessageFactoryInterface;
use Google\Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;

final class Mailer extends BaseMailer
{
    private Client $client;
    private Google_Service_Gmail $connection;
    private string $emailSender;
    private string $tokenInfoFile;

    public function __construct(
        ConfigInterface $config,
        MessageFactoryInterface $messageFactory,
        MessageBodyRenderer $messageBodyRenderer,
        EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($messageFactory, $messageBodyRenderer, $eventDispatcher);

        // get params
        $params = $config->get('params');
        $configFile = $params['googleService']['configFile'];
        $this->emailSender = $params['google/mailer']['emailSender'];
        $this->tokenInfoFile = $params['google/mailer']['tokenInfoFile'];

        // create client
        $this->client = new Client();
        $this->client->setAuthConfig($configFile);
        $this->client->setScopes(
            [
                \Google_Service_Gmail::GMAIL_SEND
            ]
        );
        $this->client->setSubject($this->emailSender);
        $this->client->setAccessType("offline");

        // set token
        $this->setToken();

        // set connection
        $this->connection = new Google_Service_Gmail($this->client);
    }

    public function sendMessage(MessageInterface $message): void
    {
        $message = $message->getSymfonyEmail()->toString();
        $gMessage = new Google_Service_Gmail_Message();
        $gMessage->setRaw($this->base64UrlEncode($message));
        $this->connection->users_messages->send($this->emailSender, $gMessage);
    }





    private function setToken(): void
    {
        $tokenInfo = $this->getTokenInfo();

        if (!$tokenInfo || $tokenInfo->accessTokenExpiresDate <= date("Y-m-d H:i:s")) {
            // the same as refreshTokenWithAssertion()
            $tokenInfo = $this->client->fetchAccessTokenWithAssertion();
            // Ex: ( [access_token] =>...  [expires_in] => 3599 [token_type] => Bearer [created] => 1723541278 )
            $tokenInfo = $this->saveTokenInfo($tokenInfo);
        };

        $this->client->setAccessToken($tokenInfo->accessToken);
    }

    private function getTokenInfo(): ?object
    {
        if (!file_exists($this->tokenInfoFile)) {
            return null;
        }

        return json_decode(file_get_contents($this->tokenInfoFile));
    }

    private function saveTokenInfo(array $tokenInfo): object
    {
        if (empty($tokenInfo) || !empty($tokenInfo['error'])) {
            throw new \RuntimeException("Unable to refresh gmail access token. Response: " . json_encode($tokenInfo));
        }

        $tokenDate = new \DateTimeImmutable();
        $tokenExpiresDate = $tokenDate->setTimestamp($tokenInfo['created'])->modify("+{$tokenInfo['expires_in']} seconds");
        file_put_contents(
            $this->tokenInfoFile,
            json_encode([
                'email' => $this->emailSender,
                'accessToken' => $tokenInfo['access_token'],
                'accessTokenExpiresDate' => $tokenExpiresDate->format("Y-m-d H:i:s"),
            ])
        );

        return json_decode(file_get_contents($this->tokenInfoFile));
    }

    private function base64UrlEncode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
