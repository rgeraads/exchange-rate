<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use Money\Currency;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument as Arg;
use Psr\Http\Message\ResponseInterface;

final class CurrencyConverterApiExchangeRateRetrieverTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_retrieve_the_exchange_rate()
    {
        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn('{"query":{"count":1},"results":{"USD_EUR":{"fr":"USD","id":"USD_EUR","to":"EUR","val":0.9211}}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('USD');

        $exchangeRateRetriever = new CurrencyConverterApiExchangeRateRetriever($client->reveal(), $baseCurrency);
        $this->assertSame(0.9211, $exchangeRateRetriever->getFor($currency));
    }
}
