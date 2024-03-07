<?php

namespace StudServis\Dreamkas\tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use StudServis\Dreamkas\Api;
use StudServis\Dreamkas\CustomerAttributes;
use StudServis\Dreamkas\exceptions\ValidationException;
use StudServis\Dreamkas\Payment;
use StudServis\Dreamkas\Position;
use StudServis\Dreamkas\Receipt;
use StudServis\Dreamkas\TaxMode;
use GuzzleHttp\Exception\ClientException;


/**
 * Class ApiTest
 */
class ApiTest extends \PHPUnit_Framework_TestCase
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
        $client = $this->createMockClient([
            new Response(200, ['X-Foo' => 'Bar'], $jsonData),
        ]);

        $api = new Api('FAKE', 123, $client);

        $receipt = new Receipt([
            'type'    => 'SALE',
            'timeout' => 300,
            'taxMode' => 'SIMPLE_WO',
        ]);
        $receipt->positions[] = new Position([
            'name'     => 'Консультационные услуги',
            'type'     => 'SERVICE',
            'quantity' => 1,
            'price'    => 116700.0,
            'priceSum' => 0,
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
        } catch (ValidationException $e) {
            $this->assertFalse(true, 'Validation exception: ' . $e->getMessage());
        } catch (ClientException $e) {
            echo $e->getResponse()->getBody();
            $this->assertFalse(true, 'Client exception');
        }
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('createdAt', $response);
    }

    private function createMockClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

}
