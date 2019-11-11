<?php

namespace bitrix\rest\client;

use bitrix\exception\BitrixServerException;
use bitrix\exception\TransportException;
use bitrix\exception\UndefinedBitrixServerException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class WebHook implements BitrixClient
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

    public function info(): string
    {
        return "Web hook " . $this->webHookBase . "/";
    }

    public function call(string $method, array $parameters = [])
    {
        try {
            $response = $this->client->request('POST', "$this->webHookBase/$method.json", [
                RequestOptions::FORM_PARAMS => $parameters,
            ]);
        } catch (GuzzleException $exception) {
            throw new TransportException("This exception should not be ever happening: " . $exception->getMessage());
        }
        try {
            $decoded = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $exception) {
            throw new TransportException("Failed to decode result: " . $exception->getMessage());
        }
        if (!empty($decoded['error'])||!empty($decoded['error_description'])) {
            throw new BitrixServerException("{$decoded['error']}: {$decoded['error_description']}");
        }
        if ($response->getStatusCode() !== 200) {
            throw new UndefinedBitrixServerException($response->getStatusCode() . ": " . $response->getReasonPhrase());
        }
        $result = $decoded['result'];
        if (isset($decoded['total'])) {
            $result = [
                'result' => $decoded['result'],
                'total'  => $decoded['total'],
            ];
            if (isset($decoded['next'])) {
                $result['next'] = $decoded['next'];
            }
        }
        return $result;
    }
}