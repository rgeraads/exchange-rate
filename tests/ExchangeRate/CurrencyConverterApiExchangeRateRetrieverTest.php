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
        $response->getBody()->willReturn('{"query":{"count":1},"results":{"EUR_USD":{"id":"EUR_USD","val":1.154888,"to":"USD","fr":"EUR"}}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('USD');

        $exchangeRateRetriever = new CurrencyConverterApiExchangeRateRetriever($client->reveal(), $baseCurrency, null);
        $this->assertSame(1.154888, $exchangeRateRetriever->getFor($currency));
    }

    /**
     * @test
     */
    public function it_should_not_make_an_api_call_for_the_same_currencies()
    {
        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->shouldNotBeCalled();
        $response->getBody()->shouldNotBeCalled();

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->shouldNotBeCalled();

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('EUR');

        $exchangeRateRetriever = new CurrencyConverterApiExchangeRateRetriever($client->reveal(), $baseCurrency, null);
        $this->assertSame(1.0, $exchangeRateRetriever->getFor($currency));
    }

    /**
     * @test
     * @expectedException \ExchangeRate\CurrencyConverterApiException
     * @expectedExceptionMessage Sorry, currency "FOO" not found in list of currencies.
     */
    public function it_should_throw_if_the_currency_can_not_be_found()
    {
        /** @var ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn('{"query":{"count":0},"results":{}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        $baseCurrency = new Currency('EUR');
        $currency     = new Currency('FOO');

        $exchangeRateRetriever = new CurrencyConverterApiExchangeRateRetriever($client->reveal(), $baseCurrency, null);
        $exchangeRateRetriever->getFor($currency);
    }
}
