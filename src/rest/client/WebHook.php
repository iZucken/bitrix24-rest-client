<?php

namespace bitrix\rest\client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;

class WebHook extends AbstractBitrix24 implements Bitrix24
{
    /**
     * @var Client
     */
    private $client = null;
    private $webHookBase = null;

    function __construct(string $webHookBase)
    {
        $jar = new CookieJar();
        $this->client = new Client([
            RequestOptions::COOKIES         => $jar,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS     => false,
        ]);
        $this->webHookBase = $webHookBase;
    }

    protected function getBaseLink(): string
    {
        return $this->webHookBase . "/";
    }

    protected function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function info(): string
    {
        return "Web hook " . parent::info();
    }

    public function call(string $method, array $parameters = [])
    {
        return parent::call($method, $parameters);
    }
}