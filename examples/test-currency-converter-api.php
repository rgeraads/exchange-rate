<?php

use ExchangeRate\CurrencyConverterApiExchangeRateRetriever;
use GuzzleHttp\Client;
use Money\Currency;

require_once __DIR__ . '/../vendor/autoload.php';

$client       = new Client();
$baseCurrency = new Currency('EUR');
$currency     = new Currency('USD');

$exchangeRateConverter = new CurrencyConverterApiExchangeRateRetriever($client, $baseCurrency, null);

$exchangeRate = $exchangeRateConverter->getFor($currency);

var_dump($exchangeRate);
