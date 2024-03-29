[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/carpenstar/bybitapi-sdk-core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/carpenstar/bybitapi-sdk-core/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/carpenstar/bybitapi-sdk-core/badges/build.png?b=master)](https://scrutinizer-ci.com/g/carpenstar/bybitapi-sdk-core/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/carpenstar/bybitapi-sdk-core/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

# Bybit SDK

***DISCLAIMER: Это неофициальный SDK, от независимого разработчика.   
Биржа ByBit не несет ответственность за работу этого кода, так же как и разработчик SDK не несет отвественность за работоспособность АПИ ByBit   
Любые интересующие вас вопросы относительно настройки, информацию о найденных багах или благодарности (слова ненависти :-)) вы можете в оставить в Issues или написав на почту mighty.vlad@gmail.com (ru, en)***

## Cодержание:
* [Требования](https://github.com/carpenstar/bybitapi-sdk-core#требования)
* [Установка](https://github.com/carpenstar/bybitapi-sdk-core#установка)
* [Простые примеры использования](https://github.com/carpenstar/bybitapi-sdk-core#простые-примеры-использования)
* [Как использовать](https://github.com/carpenstar/bybitapi-sdk-core#как-использовать)
* [Список доступных эндпоинтов](https://github.com/carpenstar/bybitapi-sdk-core#список-доступных-эндпоинтов)
* [Как переопределять компоненты?](https://github.com/carpenstar/bybitapi-sdk-core#как-переопределять-компоненты)
* [Важные обьекты и словари ядра](https://github.com/carpenstar/bybitapi-sdk-core#важные-обьекты-и-словари-ядра)
* [Дорожная карта](https://github.com/carpenstar/bybitapi-sdk-core#дорожная-карта)


## Требования

- PHP >= 7.4

## Установка

[SPOT-trading package](https://github.com/carpenstar/bybitapi-sdk-spot)
```sh 
composer require carpenstar/bybitapi-sdk-spot
```


[DERIVATIVES-trading package](https://github.com/carpenstar/bybitapi-sdk-derivatives)
```sh 
composer require carpenstar/bybitapi-sdk-derivatives
```


[WEBSOCKETS channels](https://github.com/carpenstar/bybitapi-sdk-websockets)
```sh 
composer require carpenstar/bybitapi-sdk-websockets
```

## Простые примеры использования:
* [Simple data farm](https://github.com/carpenstar/bybit-data-farm-example) (Websockets - Market data)
* [Simple widget with tickers](https://github.com/carpenstar/bybitapi-widget-example) (Websockets - Market data)

## Как использовать:
В первую очередь вам нужно сделать api-key и secret в следующих разделах биржи:   
https://www.bybit.com/app/user/api-management - основная сеть (production)   
https://testnet.bybit.com/app/user/api-management - тестовая сеть (testnet)

Экземпляр приложения sdk:

```php
use Carpenstar\ByBitAPI\BybitAPI;


new BybitAPI($host, $apiKey, $secret);
```

### Виды обращений к апи:

АПИ биржи предусматривает два варианта взаимодействия - это синхронные REST-запросы или подписка на каналы websockets.
Ниже приведена схема вызова функции, с примером запроса.   
Полную информацию об эндпоинтах, параметрах и результатах запроса следует смотреть на странице подключаемого пакета.

### REST-queries (пакеты: SPOT, DERIVATIVES)
```php
// Entrypoint:

BybitAPI::rest(
    string $endpointClassname,  // Имя класса эндпоинта, содержащий в себе все необходимые инструкции для обращений к апи
    [?IRequestInterface $options = null], // (обязательность смотри в описании интерфейсе обьекта параметров эндпоинта) Обьект с набором get или post параметров, которые запрашивает эндпоинт биржи
    [?int $outputMode = EnumOutputMode::DEFAULT_MODE] // Режим вывода результата запроса к апи биржи, по умолчанию, ответ от апи преобразуется в dto.
): IResponseInterface;




// REST example:

use Carpenstar\ByBitAPI\BybitAPI;
use Carpenstar\ByBitAPI\Spot\MarketData\OrderBook\OrderBook;
use Carpenstar\ByBitAPI\Spot\MarketData\OrderBook\Response\OrderBookResponse;
use Carpenstar\ByBitAPI\Spot\MarketData\OrderBook\Request\OrderBookRequest;
use Carpenstar\ByBitAPI\Spot\MarketData\OrderBook\Response\OrderBookPriceResponse;

$bybit = new BybitAPI('https://api-testnet.bybit.com',"apiKey", "secret");

$options = (new OrderBookRequest())
    ->setSymbol("ATOMUSDT")
    ->setLimit(5);

/** @var OrderBookResponse $orderBookData */
$orderBookData = $bybit->rest(OrderBook::class, $options)->getBody()->fetch();

echo "Time: {$orderBookData->getTime()->format('Y-m-d H:i:s')}" . PHP_EOL;
echo "Bids:" . PHP_EOL;
/** @var OrderBookPriceResponse $bid */
foreach ($orderBookData->getBids()->all() as $bid) {
    echo " - Bid Price: {$bid->getPrice()} Bid Quantity: {$bid->getQuantity()}" . PHP_EOL;
}
echo "Asks:" . PHP_EOL;
/** @var OrderBookPriceResponse $ask */
foreach ($orderBookData->getAsks()->all() as $ask) {
    echo " - Bid Price: {$ask->getPrice()} Bid Quantity: {$ask->getQuantity()}" . PHP_EOL;
}

/**
 * Result:
 * 
 * Time: 2023-05-12 10:15:41
 * Bids:
 * - Bid Price: 171.45 Bid Quantity: 19.29
 * - Bid Price: 104.15 Bid Quantity: 9.96
 * - Bid Price: 90.25 Bid Quantity: 99.72
 * - Bid Price: 81.05 Bid Quantity: 0.75
 * - Bid Price: 16.7 Bid Quantity: 5.98
 * Asks:
 * - Bid Price: 702.85 Bid Quantity: 1639.55
 * - Bid Price: 702.9 Bid Quantity: 0.01
 * - Bid Price: 703 Bid Quantity: 0.01
 * - Bid Price: 703.25 Bid Quantity: 0.01
 * - Bid Price: 704.8 Bid Quantity: 179.16
 */
```

### Websockets-подключения (пакет: WebSockets)
```php
// Entrypoint:

BybitAPI::websocket(
    string $webSocketChannelClassName,  // Имя класса базового канала, содержащий в себе все необходимые инструкции для соединения
    IWebSocketArgumentInterface $argument, // Обьект опций который необходим для настройки соединения
    IChannelHandlerInterface $channelHandler, // Пользовательский коллбэк сообщений пришедших от сервера.
    [int $mode = EnumOutputMode::MODE_ENTITY], // Тип сообщений передаваемых в коллбэк (dto или json)
    [int $wsClientTimeout = IWebSocketArgumentInterface::DEFAULT_SOCKET_CLIENT_TIMEOUT] // Таймаут сокет-клиента в милисекундах. По умолчанию: 1000
): void




// Websockets example:

use Carpenstar\ByBitAPI\BybitAPI;
use Carpenstar\ByBitAPI\WebSockets\Channels\Spot\PublicChannels\PublicTrade\KlineChannel;
use Carpenstar\ByBitAPI\WebSockets\Channels\Spot\PublicChannels\PublicTrade\Argument\KlineArgument;
use Carpenstar\ByBitAPI\Core\Enums\EnumIntervals;
use SomethingNameSpace\Directory\CustomChannelHandler;

$wsArgument = new KlineArgument(EnumIntervals::HOUR_1, "BTCUSDT");
$callbackHandler = new CustomChannelHandler();

$bybit = new BybitAPI("https://api-testnet.bybit.com", "apiKey", "secret");
$bybit->websocket(KlineChannel::class, $wsArgument, $callbackHandler);
```

## Список доступных эндпоинтов:

* [SPOT](https://github.com/carpenstar/bybitapi-sdk-spot)
  * MARKET DATA
    - [Best Bid Ask Price](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---best-bid-ask-price)
    - [Instrument Info](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---instrument-info)
    - [Kline](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---kline)
    - [Last Traded Price](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---last-traded-price)
    - [Merged Order Book](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---merged-order-book)
    - [Public Trading Records](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---public-trading-records)
    - [Tickers](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---tickers)
    - [Order Book](https://github.com/carpenstar/bybitapi-sdk-spot#market-data---order-book)
  * TRADE
    - [Place Order](https://github.com/carpenstar/bybitapi-sdk-spot#trade---place-order)
    - [Get Order](https://github.com/carpenstar/bybitapi-sdk-spot#trade---get-order)
    - [Cancel Order](https://github.com/carpenstar/bybitapi-sdk-spot#trade---cancel-order)


* [DERIVATIVES](https://github.com/carpenstar/bybitapi-sdk-derivatives)
  * MARKET DATA
    - [Funding Rate History](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---funding-rate-history)
    - [Index Price Kline](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---index-price-kline)
    - [Instrument Info](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---instrument-info)
    - [Kline](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---kline)
    - [Mark Price Kline](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---mark-price-kline)
    - [Open Interest](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---open-interest)
    - [Order Book](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---order-book)
    - [Public Trading History](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---public-trading-history)
    - [Risk Limit](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---risk-limit)
    - [Ticker Info](https://github.com/carpenstar/bybitapi-sdk-derivatives#market-data---ticker-info)
  * CONTRACT
    - ACCOUNT
      - [Get Trading Fee Rate](https://github.com/carpenstar/bybitapi-sdk-derivatives#contract---account---get-trading-fee-rate)
      - [Wallet Balance](https://github.com/carpenstar/bybitapi-sdk-derivatives#contract---account---wallet-balance)
    - ORDER
      - [Place Order](https://github.com/carpenstar/bybitapi-sdk-derivatives#contract---account---order---place-order)


* [WEBSOCKETS](https://github.com/carpenstar/bybitapi-sdk-websockets)
  * SPOT
    - PUBLIC CHANNEL
      - [Order Book](https://github.com/carpenstar/bybitapi-sdk-websockets#public-channel---order-book)
      - [Bookticker](https://github.com/carpenstar/bybitapi-sdk-websockets#public-channel---bookticker)
      - [Tickers](https://github.com/carpenstar/bybitapi-sdk-websockets#public-channel---tickers)
      - [Public Trade](https://github.com/carpenstar/bybitapi-sdk-websockets#public-channel---public-trade)
  * DERIVATIVES
    - PUBLIC CHANNEL
      - [Order Book](https://github.com/carpenstar/bybitapi-sdk-websockets#public-channel---order-book-1)

## Как переопределять компоненты?
### REST - запросы

<hr/>

### Переопределение Endpoint:
Если вы хотите переопределить поведение или расширить класс Endpoint то у вас есть два возможных варианта действия:
- Создать новый класс, который наследует уже существующему классу Endpoint'a (предпочтительно)
```php
use Carpenstar\ByBitAPI\Spot\MarketData\OrderBook\OrderBook;

class CustomOrderBook extends OrderBook
{
  // Какой-то код
}
```
- Создать новый класс, определив для него родителя - PublicEndpoint или PrivateEndpoint и имплементировать интерфейс IGetEndpointInterface или IPostEndpointInterface
```php
namespace Source;

use Carpenstar\ByBitAPI\Core\Interfaces\IGetEndpointInterface;
use Carpenstar\ByBitAPI\Core\Endpoints\PublicEndpoint;

class CustomOrderBook extends PublicEndpoint implements IGetEndpointInterface
{
    // Какой-то код
}
```

<hr/>


### Переопределение Request-параметров эндпоинта:
При необходимости поменять, что-то в классе передаваемых в апи параметров, вы можете создать новый класс
отнаследовавшись либо от существующего, либо от абстрактного класса AbstractParameters.

```php
namespace Source;
use Carpenstar\ByBitAPI\Core\Objects\AbstractParameters;

class CustomRequestParameters extends AbstractParameters 
{
    // ...
    public function setSymbol(string $symbol): self
    {
        $this->symbol = substr($symbol, 0, -4);
        return $this;
    }    
    // ...
}
```


Предпочтительнее наследоваться от уже существующих dto, потому что в противном случае, вам придется создать новый класс эндпоинта с измененной функцией `getRequestClassname()`
```php
use Source\CustomOrderBook;

use Carpenstar\ByBitAPI\Core\Interfaces\IGetEndpointInterface;
use Carpenstar\ByBitAPI\Core\Endpoints\PublicEndpoint;

class CustomOrderBook extends PublicEndpoint implements IGetEndpointInterface 
{
    // ...
    protected function getRequestClassname(): string
    {
        return OrderBookRequest::class;
    }
    // ...
}
```
Чтобы подключить новый обьект параметров, нужно при вызове метода BybitAPI::rest() передать новый CustomRequestParameters в качестве второго аргумента.
```php
use Carpenstar\ByBitAPI\BybitAPI; 
use Source\CustomOrderBook;
use Source\CustomRequestParameters;

$bybit = new BybitAPI("host", "apiKey", "secret");
$bybit->rest(CustomOrderBook::class, (new CustomRequestParameters())->setSymbol("BTCUSDT"))
```
<hr />

### Переопределение dto-ответа
```php
namespace Source;

class CustomOrderBookResponse extends AbstractResponse
{
    private string $symbol;
    
    public function __construct(array $data)
    {
        $this->symbol = $data['symbol'];
    }
}
```

<hr/>

## Важные обьекты и словари ядра:


### IResponseInterface

```php
interface IResponseInterface
{
    public function getReturnCode(): int;
    public function getReturnMessage(): string;
    public function getBody(): ICollectionInterface;
    public function getReturnExtendedInfo(): array;
    public function getTime(): \DateTime;

    public function bindEntity(string $className);
    public function handle(int $outputMode): IResponseInterface;
}
```
<hr/>
<br />

### Collections:

#### \Carpenstar\ByBitAPI\Core\Objects\Collection\ArrayCollection ::ICollectionInterface

```php
class ArrayCollection
{
    public function push(?array $item = null): self // Добавление элемента в текущую коллекцию
    public function all(): array; // Извлечение всего содержимого коллекции
    public function fetch(); // Извлечение ТЕКУЩЕГО элемента коллекции и передвижение курсора на СЛЕДУЮЩИЙ элемент коллекции
    public function count(): int; // Количество элементов коллекции
}
```

#### \Carpenstar\ByBitAPI\Core\Objects\Collection\EntityCollection ::ICollectionInterface

```php
class ArrayCollection
{
    public function push(?IResponseEntityInterface $item = null): self // Добавление элемента в текущую коллекцию
    public function all(): array; // Извлечение всего содержимого коллекции
    public function fetch(); // Извлечение ТЕКУЩЕГО элемента коллекции и передвижение курсора на СЛЕДУЮЩИЙ элемент коллекции
    public function count(): int; // Количество элементов коллекции
}
``` 

#### \Carpenstar\ByBitAPI\Core\Objects\Collection\StringCollections ::ICollectionInterface

```php
class ArrayCollection
{
    public function push(?string $item = null): self // Добавление элемента в текущую коллекцию
    public function all(): array; // Извлечение всего содержимого коллекции
    public function fetch(); // Извлечение ТЕКУЩЕГО элемента коллекции и передвижение курсора на СЛЕДУЮЩИЙ элемент коллекции
    public function count(): int; // Количество элементов коллекции
}
```
<hr/>
<br />

### Helpers:

#### \Carpenstar\ByBitAPI\Core\Helpers\DateTimeHelper

```php
namespace Carpenstar\ByBitAPI\Core\Helpers;

class DateTimeHelper
{
    public static function makeFromTimestamp(int $timestamp): \DateTime // Преобразует timestamp ответа (uint64) в обьект DateTime
    public static function makeTimestampFromDateString(string $datetime): int // Преобразует строку даты/времени в таймштамп cо значением миллисекунд (unit64) 
}
```
<hr/>
<br />

### Dictionaries:

#### \Carpenstar\ByBitAPI\Core\Enums\EnumDerivativesCategory
Справочник содержит в себе тип дериватного ордера (линейный, инверсивный, опцион)
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumDerivativesCategory
{
    const CATEGORY_PRODUCT_LINEAR = 'linear';
    const CATEGORY_PRODUCT_INVERSE = 'inverse';
    const CATEGORY_PRODUCT_OPTION = 'option';

}
```
<br />

#### \Carpenstar\ByBitAPI\Core\Enums\EnumHttpMethods
Справочник содержит тип http-запроса
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumHttpMethods
{
    const GET = "GET";
    const POST = "POST";
}
```
<br />

#### \Carpenstar\ByBitAPI\Core\Enums\EnumIntervals
Справочник содержит перечень временных интервалов свечей
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumIntervals
{
    const MINUTE1 = '1m'; // 1 minute
    const MINUTE_3 = '3m'; // 3 minutes
    const MINUTE_5 = '5m'; // 5 minutes
    const MINUTE_15 = '15m'; // 15 minutes
    const MINUTE_30 = '30m'; // 30 minutes
    const HOUR_1 = '1h'; // 1 hour
    const HOUR_2 = '2h'; // 2 hours
    const HOUR_4 = '4h'; // 4 hours
    const HOUR_6 = '6h'; // 6 hours
    const HOUR_12 = '12h'; // 12 hours
    const DAY_1 = '1d'; // 1 day
    const WEEK_1 = '1w'; // 1 week
    const MONTH_1 = '1m'; // 1 month
}
```
<br />

#### \Carpenstar\ByBitAPI\Core\Enums\EnumOrderCategory
Справочник режимов выставления завершения ордера (обычный или TakeProfit/StopLoss)
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumOrderCategory
{
    const NORMAL_ORDER = 0; // Default mode
    const TPSL_ORDER = 1; // TakeProfit/StopLoss mode
}
```
<br />

#### \Carpenstar\ByBitAPI\Core\Enums\EnumOrderType
Справочник режимов исполнения ордеров: лимитный, по рынку, условный
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumOrderType
{
    const LIMIT = "Limit";
    const MARKET = "Market";
    const LIMIT_MAKER = "Limit_maker";
}
```
<br />

#### \Carpenstar\ByBitAPI\Core\Enums\EnumOutputMode
Справочник форматов которые может выводить sdk
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumOutputMode
{
    const MODE_ENTITY = 0;
    const MODE_ARRAY = 1;
    const MODE_JSON = 2;
}
```
<br />

#### \Carpenstar\ByBitAPI\Core\Enums\EnumSide
Справочник направлений ордера: продажа или покупка
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumSide
{
    const BUY = "Buy";
    const SELL = "Sell";
}
```
<br />

#### \Carpenstar\ByBitAPI\Core\Enums\EnumTimeInForce
Справочник типа исполнения ордера
```php
namespace Carpenstar\ByBitAPI\Core\Enums;

interface EnumTimeInForce 
{
    const GOOD_TILL_CANCELED = "GTC";
    const FILL_OR_KILL = "FOK";
    const IMMEDIATE_OR_CANCEL = "IOC";
}
```
