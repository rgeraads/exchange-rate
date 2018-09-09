<?php

namespace ExchangeRate;

final class CurrencyConverterApiException extends ExchangeRateException
{
    /**
     * @param string $currencyCode
     *
     * @return self
     */
    public static function currencyNotFound(string $currencyCode): self
    {
        return new self(sprintf('Sorry, currency "%s" not found in list of currencies.', $currencyCode));
    }
}
