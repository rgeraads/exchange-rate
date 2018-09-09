<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Money\Currency;

final class CurrencyConverterApiExchangeRateRetriever implements ExchangeRateRetriever
{
    private const EXCHANGE_RATE_API_URL = 'https://free.currencyconverterapi.com';

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
     *
     * @throws GuzzleException
     * @throws CurrencyConverterApiException
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
     *
     * @throws GuzzleException
     * @throws CurrencyConverterApiException
     */
    private function retrieveExchangeRateFor(Currency $currency): void
    {
        $conversion = sprintf('%s_%s', $this->baseCurrency->getCode(), $currency->getCode());

        $response = $this->client->request('GET', self::EXCHANGE_RATE_API_URL . '/api/v6/convert', [
            'query' => ['q' => $conversion]
        ]);

        $data = json_decode($response->getBody(), true);

        if (! array_key_exists($conversion, $data['results'])) {
            throw CurrencyConverterApiException::currencyNotFound($currency->getCode());
        }

        $this->exchangeRates[$currency->getCode()] = (float) $data['results'][$conversion]['val'];
    }
}
