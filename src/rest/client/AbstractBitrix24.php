<?php

namespace bitrix\rest\client;

use bitrix\exception\BitrixServerException;
use bitrix\exception\TransportException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
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
        try {
            $response = $this->getClient()->request('POST', $this->getBaseLink() . "$method.json", [
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