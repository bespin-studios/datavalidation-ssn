<?php

namespace Bespin\DataValidation;

use Bespin\DataValidation\SSN\Helper\ValidatorFactory;
use Bespin\DataValidation\SSN\Helper\ValidatorInterface;

class SSN
{
    public static function verify(string $ssn, Country $country, bool $isAlreadyMachineFormat = false): bool
    {
        // Get the appropriate validator for the given country
        $validator = ValidatorFactory::getValidator($country);

        // Delegate verification to the country-specific validator
        return $validator->verify($ssn, $isAlreadyMachineFormat);
    }

    public static function format(string $ssn, Country $country, Format $format = Format::machine, bool $isAlreadyMachineFormat = false): string
    {
        // Get the appropriate validator for the given country
        $validator = ValidatorFactory::getValidator($country);

        // Delegate formatting to the country-specific validator
        return $validator->format($ssn, $format, $isAlreadyMachineFormat);
    }

    public static function getCountryValidator(Country $country): ValidatorInterface
    {
        return ValidatorFactory::getValidator($country);
    }
}