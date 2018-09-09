<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Money\Currency;

final class FixerIoExchangeRateRetriever implements ExchangeRateRetriever
{
    private const EXCHANGE_RATE_API_URL = 'data.fixer.io/api';

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
     * @var string
     */
    private $apiKey;

    /**
     * @inheritdoc
     */
    public function __construct(ClientInterface $client, Currency $baseCurrency, string $apiKey = null)
    {
        $this->client       = $client;
        $this->baseCurrency = $baseCurrency;
        $this->apiKey       = $apiKey;
    }

    /**
     * @inheritdoc
     *
     * @throws FixerIoException
     * @throws GuzzleException
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
     * Retrieves exchange rates from https://fixer.io
     *
     * @param Currency $currency
     *
     * @throws FixerIoException
     * @throws GuzzleException
     */
    private function retrieveExchangeRateFor(Currency $currency): void
    {
        $response = $this->client->request('GET', self::EXCHANGE_RATE_API_URL . '/latest', [
            'query' => [
                'access_key' => $this->apiKey,
                'base'       => $this->baseCurrency->getCode(),
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data['success'] !== true) {
            throw FixerIoException::couldNotRetrieveRates($data['error']);
        }

        if (! array_key_exists($currency->getCode(), $data['rates'])) {
            throw FixerIoException::currencyNotFound($currency->getCode());
        }

        $this->exchangeRates[$currency->getCode()] = $data['rates'][$currency->getCode()];
    }
}
