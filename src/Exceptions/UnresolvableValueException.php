<?php

namespace TreptowLabs\Envelope\Exceptions;

class UnresolvableValueException extends \Exception
{
    public function __construct(public readonly string $key, public readonly mixed $value)
    {
        parent::__construct('Unresolvable value provided for ['.$key.']');
    }
}
