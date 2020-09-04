<?php

namespace bitrix\rest\client;

use bitrix\exception\TransportException;
use bitrix\rest\OauthFullCredentials;
use bitrix\storage\Storage;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

class OauthAutoLogin extends AbstractConnection implements BitrixClient
{
    const ACCESS_TOKEN_DURATION = '+55 minutes';
    const REFRESH_TOKEN_DURATION = "+25 days";
    /**
     * @var Client
     */
    protected $client = null;
    private $baseLink = null;
    /**
     * @var OauthFullCredentials
     */
    private $credentials;
    /**
     * @var Storage
     */
    private $storage;
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $accessToken = null;
    private $accessUntil = null;
    private $refreshToken = null;
    private $refreshUntil = null;

    function __construct(
        string $baseLink,
        OauthFullCredentials $credentials,
        Storage $storage,
        LoggerInterface $logger
    ) {
        $this->client = new Client([
            RequestOptions::COOKIES         => new CookieJar(),
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS     => false,
        ]);
        $this->baseLink = $baseLink;
        $this->credentials = $credentials;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    public function info(): string
    {
        return "REST Application " . $this->baseLink . "/rest/";
    }

    /**
     * @return array
     * @throws TransportException
     */
    public function newRefreshToken() : array
    {
        $this->logger->info("Logging in");
        $response = $this->client->get($this->baseLink . "/oauth/authorize/", [
            RequestOptions::QUERY => [
                'client_id' => $this->credentials->id,
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new TransportException("Failed oauth request");
        }
        $response = $this->client->post($this->baseLink . "/oauth/authorize/", [
            RequestOptions::FORM_PARAMS => [
                'client_id'     => $this->credentials->id,
                'backurl'       => '/oauth/authorize/?client_id=' . $this->credentials->id,
                'AUTH_FORM'     => 'Y',
                'TYPE'          => 'AUTH',
                'USER_LOGIN'    => $this->credentials->user,
                'USER_PASSWORD' => $this->credentials->password,
            ],
        ]);
        if ($response->getStatusCode() !== 302) {
            throw new TransportException("Failed oauth redirect, got code " . $response->getStatusCode());
        }
        $this->logger->info("Getting new refresh token");
        $params = [];
        parse_str(parse_url($response->getHeaderLine('Location'), PHP_URL_QUERY), $params);
        $response = $this->client->get("https://oauth.bitrix.info/oauth/token/", [
            RequestOptions::QUERY => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->credentials->id,
                'client_secret' => $this->credentials->secret,
                'code'          => $params['code'],
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new TransportException("Failed first token grant");
        }
        try {
            $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (Exception $exception) {
            throw new TransportException("Failed to decode first token grant");
        }
        return $data;
    }

    /**
     * @param string $refreshToken
     * @return array
     * @throws TransportException
     */
    public function newAccessToken(string $refreshToken) : array
    {
        $this->logger->info("Getting new access token");
        $response = $this->client->get("https://oauth.bitrix.info/oauth/token/", [
            RequestOptions::QUERY => [
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->credentials->id,
                'client_secret' => $this->credentials->secret,
                'refresh_token' => $refreshToken,
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new TransportException("Failed to request refresh token $refreshToken");
        }
        try {
            $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (Exception $exception) {
            throw new TransportException("Failed to decode refresh response $refreshToken");
        }
        if (!empty($data['error'])) {
            throw new TransportException("Failed to refresh token {$data['error']}: {$data['error_description']}");
        }
        return $data;
    }

    /**
     * @param array $data
     */
    public function storeTokens(array $data)
    {
        $this->accessToken = $data['access_token'];
        $this->accessUntil = strtotime(self::ACCESS_TOKEN_DURATION);
        $this->refreshToken = $data['refresh_token'];
        $this->refreshUntil = strtotime(self::REFRESH_TOKEN_DURATION);
        $this->storage->set('AccessToken', $this->accessToken);
        $this->storage->set('AccessTokenUntil', $this->accessUntil);
        $this->storage->set('RefreshToken', $this->refreshToken);
        $this->storage->set('RefreshTokenUntil', $this->refreshUntil);
    }

    /**
     * @throws TransportException
     */
    public function acquireTokens()
    {
        $this->refreshToken = $this->storage->get('RefreshToken');
        $this->refreshUntil = $this->storage->get('RefreshTokenUntil');
        if (empty($this->refreshToken) || empty($this->refreshUntil) || (time() >= $this->refreshUntil)) {
            $this->storeTokens($this->newRefreshToken());
            return;
        }
        $this->accessToken = $this->storage->get('AccessToken');
        $this->accessUntil = $this->storage->get('AccessTokenUntil');
        if (empty($this->accessToken) || empty($this->accessUntil) || (time() >= $this->accessUntil)) {
            $this->storeTokens($this->newAccessToken($this->refreshToken));
            return;
        }
    }

    /**
     * @return string
     * @throws TransportException
     */
    public function acquireAccessToken() : string
    {
        if (empty($this->accessToken) || empty($this->accessUntil) || (time() >= $this->accessUntil)) {
            $this->acquireTokens();
        }
        return $this->accessToken;
    }

    public function call(string $method, array $parameters = [])
    {
        $this->logger->info("Call to $method");
        $parameters['auth'] = $this->acquireAccessToken();
        $uri = "$this->baseLink/rest/$method.json";
        return parent::call($uri, $parameters);
    }

    public function purge()
    {
        $this->accessToken = null;
        $this->accessUntil = null;
        $this->refreshToken = null;
        $this->refreshUntil = null;
        $this->storage->set('AccessToken', null);
        $this->storage->set('AccessTokenUntil', null);
        $this->storage->set('RefreshToken', null);
        $this->storage->set('RefreshTokenUntil', null);
    }
}