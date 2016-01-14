<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use Money\Currency;
use Prophecy\Argument as Arg;
use Psr\Http\Message\ResponseInterface;

final class CurrencyConverterApiExchangeRateRetrieverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_retrieve_the_exchange_rate()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn('{"query":{"count":1},"results":{"USD_EUR":{"fr":"USD","id":"USD_EUR","to":"EUR","val":0.9211}}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        /** @var Currency $baseCurrency */
        $baseCurrency = $this->prophesize(Currency::class);
        $baseCurrency->getName()->willReturn('EUR');

        /** @var Currency $currency */
        $currency = $this->prophesize(Currency::class);
        $currency->getName()->willReturn('USD');

        $exchangeRateRetriever = new CurrencyConverterApiExchangeRateRetriever($client->reveal(), $baseCurrency->reveal());
        $this->assertSame(0.9211, $exchangeRateRetriever->getFor($currency->reveal()));
    }
}

