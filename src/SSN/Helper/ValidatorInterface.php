<?php

namespace Bespin\DataValidation\SSN\Helper;

use Bespin\DataValidation\Format;

interface ValidatorInterface
{
    public function verify(string $ssn, bool $isAlreadyMachineFormat = false): bool;

    public function format(string $ssn, Format $format = Format::machine, bool $isAlreadyMachineFormat = false): string;

}