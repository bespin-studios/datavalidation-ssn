<?php

namespace Bespin\DataValidation\SSN\Helper;

use Bespin\DataValidation\Country;
use Bespin\DataValidation\SSN;
use InvalidArgumentException;

class ValidatorFactory
{
    public static function getValidator(Country $country): ValidatorInterface
    {
        return match ($country) {
            Country::Germany => new SSN\Country\Germany(),
            default          => throw new InvalidArgumentException("Unsupported country: ".$country->name),
        };
    }
}