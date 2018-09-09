<?php

namespace ExchangeRate;

use Assert\Assertion as Assert;
use GuzzleHttp\ClientInterface;
use Money\Currency;

final class CurrencyConverterApiExchangeRateRetriever implements ExchangeRateRetriever
{
    private const EXCHANGE_RATE_API_URL = 'http://free.currencyconverterapi.com';

    /**
     * @var float[]
     */
    private $exchangeRates = [];

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Currency
     */
    private $baseCurrency;

    /**
     * @inheritdoc
     */
    public function __construct(ClientInterface $client, Currency $baseCurrency)
    {
        $this->client       = $client;
        $this->baseCurrency = $baseCurrency;
    }

    /**
     * @inheritdoc
     */
    public function getFor(Currency $currency): float
    {
        $this->retrieveExchangeRateFor($currency);

        return $this->exchangeRates[$currency->getName()];
    }

    /**
     * Retrieves exchange rates from http://free.currencyconverterapi.com
     *
     * @param Currency $currency
     */
    private function retrieveExchangeRateFor(Currency $currency): void
    {
        $conversion = sprintf('%s_%s', $currency->getName(), $this->baseCurrency->getName());

        $response = $this->client->request('GET', self::EXCHANGE_RATE_API_URL . '/api/v3/convert', [
            'query' => ['q' => $conversion]
        ]);

        Assert::same($response->getStatusCode(), 200);

        $rawExchangeRates = $response->getBody();;
        $exchangeRates = json_decode($rawExchangeRates, true);

        Assert::isArray($exchangeRates);
        Assert::keyExists($exchangeRates, 'results');
        Assert::keyExists($exchangeRates['results'], $conversion);
        Assert::keyExists($exchangeRates['results'][$conversion], 'val');
        Assert::numeric($exchangeRates['results'][$conversion]['val']);

        $this->exchangeRates[$currency->getName()] = (float) $exchangeRates['results'][$conversion]['val'];
    }
}
