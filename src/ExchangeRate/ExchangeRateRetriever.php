<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use Money\Currency;

interface ExchangeRateRetriever
{
    /**
     * @param ClientInterface $client
     * @param Currency        $baseCurrency
     * @param string          $apiKey
     */
    public function __construct(ClientInterface $client, Currency $baseCurrency, string $apiKey = null);

    /**
     * @param Currency $currency
     *
     * @return float
     */
    public function getFor(Currency $currency): float;
}
