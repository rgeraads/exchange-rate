<?php

namespace ExchangeRate;

use GuzzleHttp\ClientInterface;
use Money\Currency;

interface ExchangeRateRetriever
{
    /**
     * @param ClientInterface $client
     * @param Currency        $baseCurrency
     */
    public function __construct(ClientInterface $client, Currency $baseCurrency);

    /**
     * @param Currency $currency
     *
     * @return float
     */
    public function getFor(Currency $currency);
}
