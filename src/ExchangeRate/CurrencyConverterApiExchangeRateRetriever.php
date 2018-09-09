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
    public function __construct(ClientInterface $client, Currency $baseCurrency, string $apiKey = null)
    {
        $this->client       = $client;
        $this->baseCurrency = $baseCurrency;
    }

    /**
     * @inheritdoc
     */
    public function getFor(Currency $currency): float
    {
        if ($currency->equals($this->baseCurrency)) {
            // no need to make an api call for same currencies.
            $this->exchangeRates[$currency->getCode()] = (float) 1;
        } else {
            $this->retrieveExchangeRateFor($currency);
        }

        return $this->exchangeRates[$currency->getCode()];
    }

    /**
     * Retrieves exchange rates from https://free.currencyconverterapi.com
     *
     * @param Currency $currency
     */
    private function retrieveExchangeRateFor(Currency $currency): void
    {
        $conversion = sprintf('%s_%s', $currency->getCode(), $this->baseCurrency->getCode());

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

        $this->exchangeRates[$currency->getCode()] = (float) $exchangeRates['results'][$conversion]['val'];
    }
}
