<?php

namespace Bespin\DataValidation\SSN\Country;

use Bespin\DataValidation\Format;
use Bespin\DataValidation\SSN\Helper\ValidatorInterface;
use DateTime;

class Germany implements ValidatorInterface
{
    private array $humanFormat = [2, 8, 9, 11];

    public function verify(string $ssn, bool $isAlreadyMachineFormat = false): bool
    {
        if (!$isAlreadyMachineFormat) {
            $ssn = self::format($ssn, Format::machine, $isAlreadyMachineFormat);
        }

        // regular expression for german ssn
        $pattern = '/^\d{2}(0[1-9]|[12][0-9]|3[01])(0[1-9]|1[0-2])\d{2}[A-Z]\d{2}\d$/';
        if (!preg_match($pattern, $ssn)) {
            return false;
        }

        // german ssn has the date of birth encapsulated, check if it's valid
        if (!self::validateDate($ssn)) {
            return false;
        }

        // finally validate the checksum
        return self::validateChecksum($ssn);
    }

    public function format(string $ssn, Format $format = Format::machine, bool $isAlreadyMachineFormat = false): string
    {
        if (!$isAlreadyMachineFormat) {
            // remove all non-alphanumeric characters
            $ssn = preg_replace('/[^A-Z0-9]/', '', strtoupper($ssn));
        }

        if ($format === Format::human) {
            // add whitespace: AA DDMMYY B SS P
            foreach (array_reverse($this->humanFormat) as $format) {
                $ssn = substr_replace($ssn, '', $format, 0);
            }
        }

        return $ssn;
    }

    // german ssn has a date of birth included
    private static function validateDate(string $ssn): bool
    {
        // Extract the day, month, and year (2 digits) from SSN
        $day   = substr($ssn, 2, 2);
        $month = substr($ssn, 4, 2);
        $year  = substr($ssn, 6, 2);

        // Get the current year and century information
        $currentDate    = new DateTime();
        $currentCentury = (int)substr($currentDate->format('Y'), 0, 2); // First two digits of the current year (e.g., 20 for 2024)
        $currentYear    = (int)substr($currentDate->format('Y'), 2, 2);

        // unfortunately we have to work with a two digit year. Since a person cannot be born in the future we only have to take the current century into account or the previous one, it's highly unlikely that someone is older than 100 years and still working
        if ((intval($year)) > $currentYear) {
            $centuryModifier = -1;
        } else {
            $centuryModifier = 0;
        }
        $fullBirthDate = ($currentCentury - $centuryModifier).$year.'-'.$month.'-'.$day;
        $birthDate     = DateTime::createFromFormat('Y-m-d', $fullBirthDate);
        // if the format doesn't equal the fullBirthDate it should be a leap year issue and the date of birth is february 29th
        if ($birthDate !== false && $birthDate->format('Y-m-d') === $fullBirthDate) {
            return true;
        }
        if ($day === '29' && $month === '02' && $centuryModifier === 0) {
            //check previous century
            $fullBirthDate = ($currentCentury - 1).$year.'-'.$month.'-'.$day;
            $birthDate     = DateTime::createFromFormat('Y-m-d', $fullBirthDate);
            // if the format doesn't equal the fullBirthDate it should be a leap year issue and the date of birth is february 29th
            if ($birthDate !== false && $birthDate->format('Y-m-d') === $fullBirthDate) {
                return true;
            }
        }
        return false;
    }

    // Validierung der Prüfziffer
    private static function validateChecksum(string $ssn): bool
    {
        $elements = mb_str_split(substr($ssn, 0, 8).self::convertLetter(substr($ssn, 8, 1)).substr($ssn, 9));
        //convert all digits to int
        $elements = array_map(function (string $value) {
            return (int)$value;
        }, $elements);
        $checksum = array_pop($elements);
        $weights  = [2, 1, 2, 5, 7, 1, 2, 1, 2, 1, 2, 1];
        if (count($elements) === count($weights)) {
            $sum = 0;

            // Loop over each digit and apply the corresponding weight
            foreach ($elements as $key => $element) {
                $product = $element * $weights[$key];

                // If the product is greater than 9, sum the digits of the product (e.g., 16 -> 1 + 6 = 7)
                if ($product > 9) {
                    $product = (int)($product / 10) + ($product % 10);
                }
                $sum += $product;
            }
            // Calculate the checksum (mod10)
            $calculatedSum = $sum % 10;
            return $calculatedSum === (int)$checksum;
        }
        return false;
    }

    private static function convertLetter(string $letter): string
    {
        return str_pad(ord(strtoupper($letter)) - ord('A') + 1, 2, '0', STR_PAD_LEFT);
    }
}