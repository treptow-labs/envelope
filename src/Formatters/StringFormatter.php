<?php

namespace TreptowLabs\Envelope\Formatters;

use Stringable;

class StringFormatter implements Formatter
{
    public function format(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        return (string) $value;
    }
}
