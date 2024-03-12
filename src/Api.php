<?php

namespace StudServis\Dreamkas;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Class Api
 */
class Api
{

    public $accessToken = '';
    public $deviceId    = 0;

    /** @var Client */
    protected $client;

    //@TODO: up php version and set const to public
    const PRODUCTION_URL = 'https://kabinet.dreamkas.ru/api/';

    public function __construct(string $accessToken, int $deviceId, ClientInterface $client)
    {
        $this->accessToken = $accessToken;
        $this->deviceId = $deviceId;
        $this->client = $client;
    }


    public function request(string $method, string $uri = '', array $options = [])
    {
        if (isset($options['headers']) === false) {
            $options['headers'] = [];
        }
        $options['headers']['Authorization'] = 'Bearer ' . $this->accessToken;
        $options['headers']['Accept'] = 'application/json';
        return $this->client->request($method, $uri, $options);
    }

    public function json(string $method, string $uri = '', array $options = [])
    {

        $response = $this->request($method, $uri, $options);
        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    public function postReceipt(Receipt $receipt)
    {
        $receipt->validate();
        $data = $receipt->toArray();
        $data['deviceId'] = $this->deviceId;
        return $this->json('POST', 'receipts', [
            'json' => $data,
        ]);
    }
}