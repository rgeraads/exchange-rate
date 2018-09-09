<?php

namespace ExchangeRate;

use Assert\Assertion as Assert;
use GuzzleHttp\ClientInterface;
use Money\Currency;

final class FixerIoExchangeRateRetriever implements ExchangeRateRetriever
{
    private const EXCHANGE_RATE_API_URL = 'https://api.fixer.io';

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
        if ($currency->equals($this->baseCurrency)) {
            // fixer.io doesn't support same currencies, but we don't want it to break because of just that.
            $this->exchangeRates[$currency->getCode()] = (float) 1;
        } else {
            $this->retrieveExchangeRateFor($currency);
        }

        return $this->exchangeRates[$currency->getCode()];
    }

    /**
     * Retrieves exchange rates from http://fixer.io
     *
     * @param Currency $currency
     */
    private function retrieveExchangeRateFor(Currency $currency)
    {
        $response = $this->client->request('GET', self::EXCHANGE_RATE_API_URL . '/latest', [
            'query' => ['base' => $this->baseCurrency->getCode()]
        ]);

        Assert::same($response->getStatusCode(), 200);

        $rawExchangeRates = $response->getBody();;
        $exchangeRates = json_decode($rawExchangeRates, true);

        Assert::isArray($exchangeRates);
        Assert::keyExists($exchangeRates, 'rates');
        Assert::keyExists($exchangeRates['rates'], $currency->getCode());
        Assert::numeric($exchangeRates['rates'][$currency->getCode()]);

        $this->exchangeRates[$currency->getCode()] = $exchangeRates['rates'][$currency->getCode()];
    }
}
