<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use Money\Currency;
use Prophecy\Argument as Arg;
use Psr\Http\Message\ResponseInterface;

final class FixerIoExchangeRateRetrieverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_retrieve_the_exchange_rate()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn('{"base":"EUR","date":"2016-01-14","rates":{"AUD":1.5695,"BGN":1.9558,"BRL":4.373,"CAD":1.5647,"CHF":1.095,"CNY":7.1809,"CZK":27.021,"DKK":7.4624,"GBP":0.75703,"HKD":8.4775,"HRK":7.666,"HUF":315.97,"IDR":15155.06,"ILS":4.2986,"INR":73.4516,"JPY":128.26,"KRW":1320.77,"MXN":19.5529,"MYR":4.7855,"NOK":9.6071,"NZD":1.6903,"PHP":52.076,"PLN":4.373,"RON":4.5348,"RUB":83.2007,"SEK":9.285,"SGD":1.567,"THB":39.552,"TRY":3.3025,"USD":1.0893,"ZAR":18.0475}}');

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->willReturn($response);

        /** @var Currency $baseCurrency */
        $baseCurrency = $this->prophesize(Currency::class);
        $baseCurrency->getName()->willReturn('EUR');

        /** @var Currency $currency */
        $currency = $this->prophesize(Currency::class);
        $currency->getName()->willReturn('USD');
        $currency->equals($baseCurrency)->willReturn(false);

        $exchangeRateRetriever = new FixerIoExchangeRateRetriever($client->reveal(), $baseCurrency->reveal());
        $this->assertSame(1.0893, $exchangeRateRetriever->getFor($currency->reveal()));
    }

    /**
     * @test
     */
    public function it_should_retrieve_the_exchange_rate_for_the_same_currency()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->shouldNotBeCalled();
        $response->getBody()->shouldNotBeCalled();

        /** @var ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Arg::cetera())->shouldNotBeCalled();

        /** @var Currency $baseCurrency */
        $baseCurrency = $this->prophesize(Currency::class);
        $baseCurrency->getName()->willReturn('EUR');

        /** @var Currency $currency */
        $currency = $this->prophesize(Currency::class);
        $currency->getName()->willReturn('EUR');
        $currency->equals($baseCurrency)->willReturn(true);

        $exchangeRateRetriever = new FixerIoExchangeRateRetriever($client->reveal(), $baseCurrency->reveal());
        $this->assertSame(1.0, $exchangeRateRetriever->getFor($currency->reveal()));
    }
}
