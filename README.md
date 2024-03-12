# php-dreamkas
Фискализация чека для Дримкас-Ф для PHP 7.4

Для более старых версий PHP придётся править код на предмет типов у функций.

## Установка

```
composer require studservis/php-dreamkas
```

## Пример кода

```php
<?php
use StudServis\Dreamkas\Api;
use StudServis\Dreamkas\CustomerAttributes;
use StudServis\Dreamkas\exceptions\ValidationException;
use StudServis\Dreamkas\Payment;
use StudServis\Dreamkas\Position;
use StudServis\Dreamkas\Receipt;
use StudServis\Dreamkas\TaxMode;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;

/**
 * $accessToken - ACCESS_TOKEN из профиля
 * $deviceId - ID кассы
 */
$api = new Api(
    $accessToken,
    $deviceId,
    new Client([
        'base_uri' => Api::PRODUCTION_URL,
    ])
);
$receipt = new Receipt();
$receipt->taxMode = TaxMode::MODE_SIMPLE;
$receipt->positions[] = new Position([
    'name' => 'Билет - тест',
    'quantity' => 2,
    'price' => 210000, // цена в копейках за 1 шт. или 1 грамм
]);
$receipt->payments[] = new Payment([
    'sum' => 420000, // стоимость оплаты по чеку
]);
$receipt->attributes = new CustomerAttributes([
    'email' => 'foobar@example.com', // почта покупателя
    'phone' => '+70000000000', // телефон покупателя
]);

// Можно посчитать сумму автоматом
// $receipt->calculateSum();
// А можно завалидировать чек
// $receipt->validate();

$response = [];
try {
    $response = $api->postReceipt($receipt);
} catch (ValidationException $e) {
    // Это исключение кидается, когда информация в чеке не правильная
} catch (ClientException $e) {
    echo $e->getResponse()->getBody();
    // Это исключение кидается, когда при передачи чека в Дрикас произошла ошибка. Лучше отправить чек ещё раз
    // Если будут дубли - потом отменяйте через $receipt->type = Receipt::TYPE_REFUND;
}

```

Made by DevGroup.ru - [Создание интернет-магазинов](https://devgroup.ru/services/internet-magazin).
