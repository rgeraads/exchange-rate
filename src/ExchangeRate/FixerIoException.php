<?php

namespace ExchangeRate;

final class FixerIoException extends ExchangeRateException
{
    /**
     * @param array $data
     *
     * @return self
     */
    public static function couldNotRetrieveRates(array $data): self
    {
        return new self(sprintf('Sorry, unable to retrieve exchange rates from Fixer.io. code: %s, type: %s, info: %s', $data['code'], $data['type'], $data['info']));
    }

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
