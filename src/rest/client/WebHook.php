<?php

namespace bitrix\rest\client;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;

class WebHook extends AbstractConnection implements BitrixClient
{
    /**
     * @var Client
     */
    protected $client = null;
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

    public function info(): string
    {
        return "Web hook " . $this->webHookBase . "/";
    }

    public function call(string $method, array $parameters = [])
    {
        $uri = "$this->webHookBase/$method.json";
        return parent::call($uri, $parameters);
    }
}