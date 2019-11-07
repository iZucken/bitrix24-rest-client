<?php

namespace bitrix\rest\client;

use bitrix\exception\BitrixServerException;
use bitrix\exception\TransportException;
use bitrix\rest\client\Bitrix24;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

abstract class AbstractBitrix24 implements Bitrix24
{
    abstract protected function getBaseLink(): string;

    abstract protected function getClient(): ClientInterface;

    public function info(): string
    {
        return $this->getBaseLink();
    }

    public function call(string $method, array $parameters = [])
    {
        $response = $this->getClient()->request('POST', $this->getBaseLink() . "$method.json", [
            RequestOptions::FORM_PARAMS => $parameters,
        ]);
        try {
            $decoded = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $exception) {
            throw new TransportException("Failed to decode result: " . $exception->getMessage());
        }
        if (isset($decoded['error'])) {
            throw new BitrixServerException("{$decoded['error']}: {$decoded['error_description']}");
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