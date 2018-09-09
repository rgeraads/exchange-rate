<?php

use Dotenv\Dotenv;
use ExchangeRate\FixerIoExchangeRateRetriever;
use GuzzleHttp\Client;
use Money\Currency;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv(__DIR__ . '/../', '.env');
$dotenv->load();

$client       = new Client();
$baseCurrency = new Currency('EUR');
$currency     = new Currency('PHP');

$exchangeRateConverter = new FixerIoExchangeRateRetriever($client, $baseCurrency, getenv('FIXER_IO_API_KEY'));
$result = $exchangeRateConverter->getFor($currency);

var_dump($result);
