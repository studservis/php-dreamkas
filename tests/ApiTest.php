<?php

namespace StudServis\Dreamkas\tests;

use PHPUnit\Framework\TestCase;
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
class ApiTest extends TestCase
{

    public function testJson()
    {
        $api = new Api('FAKE', 123, Api::MODE_MOCK);
        $result = $api->json('GET', 'products');

        $this->assertSame([[]], $result);
    }

    public function testPostReceipt()
    {

        $api = new Api('FAKE', 123, Api::MODE_MOCK);

        $receipt = new Receipt();
        $receipt->taxMode = TaxMode::MODE_SIMPLE;
        $receipt->positions[] = new Position([
            'name' => 'Билет - тест',
            'quantity' => 2,
            'price' => 210000,
        ]);
        $receipt->payments[] = new Payment([
            'sum' => 420000,
        ]);
        $receipt->attributes = new CustomerAttributes([
            'email' => 'info@devgroup.ru',
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

//
        $receipt->type = Receipt::TYPE_REFUND;
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


}
