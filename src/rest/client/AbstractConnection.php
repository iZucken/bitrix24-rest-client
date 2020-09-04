<?php

namespace bitrix\rest\client;

use bitrix\exception\BitrixServerException;
use bitrix\exception\TransportException;
use bitrix\exception\UndefinedBitrixServerException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Throwable;

abstract class AbstractConnection
{
    /**
     * @var Client
     */
    protected $client = null;

    /**
     * @param string $uri
     * @param array  $parameters
     * @return mixed
     * @throws BitrixServerException
     * @throws TransportException
     * @throws UndefinedBitrixServerException
     * @throws Throwable
     */
    public function call(string $uri, array $parameters = [])
    {
        try {
            $response = $this->client->request('POST', $uri, [
                RequestOptions::FORM_PARAMS => $parameters,
            ]);
        } catch (Throwable $exception) {
            throw new TransportException("This exception should not be ever happening: " . $exception->getMessage());
        }
        $content = $response->getBody()->getContents();
        try {
            $decoded = \GuzzleHttp\json_decode($content, true);
        } catch (Exception $exception) {
            throw new TransportException("Json {$exception->getMessage()}", $exception->getCode(), $exception, $content);
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