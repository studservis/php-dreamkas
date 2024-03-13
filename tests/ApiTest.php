<?php

namespace StudServis\Dreamkas\tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use StudServis\Dreamkas\Api;
use StudServis\Dreamkas\CustomerAttributes;
use StudServis\Dreamkas\exceptions\ValidationException;
use StudServis\Dreamkas\Payment;
use StudServis\Dreamkas\Position;
use StudServis\Dreamkas\Receipt;
use GuzzleHttp\Exception\ClientException;
use StudServis\Dreamkas\TaxMode;


/**
 * Class ApiTest
 */
class ApiTest extends TestCase
{

    public function testJson()
    {
        $jsonData = json_encode([[]]);

        $client = $this->createMockClient([
            new Response(200, ['X-Foo' => 'Bar'], $jsonData),
        ]);

        $api = new Api('FAKE', 123, $client);
        $result = $api->json('GET', 'products');

        $this->assertSame([[]], $result);
    }

    public function testPostReceipt()
    {
        $jsonData = json_encode([
            'id' => '65e0dbcfbb519c0032c99980',
            'createdAt' => '2024-02-29T19:32:31.968Z',
            'status' => 'PENDING',
        ]);
        $mock = new MockHandler([new Response(200, ['X-Foo' => 'Bar'], $jsonData)]);
        $handlerStack = HandlerStack::create($mock);
        $client =  new Client(['handler' => $handlerStack, 'base_uri' => Api::PRODUCTION_URL]);

        $api = new Api('FAKE', 123, $client);

        $receipt = new Receipt([
            'type'    => 'SALE',
            'timeout' => 300,
            'taxMode' => TaxMode::MODE_SIMPLE_WO,
        ]);
        $receipt->positions[] = new Position([
            'name'     => 'Консультационные услуги',
            'type'     => 'SERVICE',
            'quantity' => 1,
            'price'    => 116700.0,
            'priceSum' => 116700.0,
            'tax'      => NULL,
            'taxSum'   => NULL,
        ]);
        $receipt->payments[] = new Payment([
            'sum'  => 116700.0,
            'type' => 'CASHLESS',
        ]);
        $receipt->attributes = new CustomerAttributes([
            'email' => 'foobar@example.com',
            'phone' => '+70000000000',
        ]);

        $receipt->calculateSum();


        $response = [];
        try {
            $response = $api->postReceipt($receipt);
            $lastRequest = $mock->getLastRequest();
        } catch (ValidationException $e) {
            $this->assertFalse(true, 'Validation exception: ' . $e->getMessage());
        } catch (ClientException $e) {
            echo $e->getResponse()->getBody();
            $this->assertFalse(true, 'Client exception');
        }

        $recipeJson = '{
            "type":"SALE",
            "timeout":300,
            "taxMode":"SIMPLE_WO",
            "positions":[
                {
                    "name":"\u041a\u043e\u043d\u0441\u0443\u043b\u044c\u0442\u0430\u0446\u0438\u043e\u043d\u043d\u044b\u0435 \u0443\u0441\u043b\u0443\u0433\u0438",
                    "type":"SERVICE",
                    "quantity":1,
                    "price":116700,
                    "priceSum":116700
                }
            ],
            "payments":[
                {
                    "sum":116700,
                    "type":"CASHLESS"
                }
            ],
            "attributes":{
                "email":"foobar@example.com",
                "phone":"+70000000000"
            },
            "total":{
                "priceSum":116700
            },
            "deviceId":123
        }';

        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('createdAt', $response);
        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertSame('/api/receipts', $lastRequest->getUri()->getPath());
        $this->assertContains('application/json', $lastRequest->getHeader('Content-Type'));
        $this->assertSame('kabinet.dreamkas.ru', $lastRequest->getUri()->getHost());
        $this->assertContains('Bearer FAKE', $lastRequest->getHeader('Authorization'));
        $this->assertContains('application/json', $lastRequest->getHeader('Accept'));
        $this->assertJsonStringEqualsJsonString($recipeJson, $lastRequest->getBody()->getContents());
    }

    private function createMockClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack, 'base_uri' => Api::PRODUCTION_URL]);
    }

}
