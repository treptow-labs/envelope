<?php

namespace TreptowLabs\Envelope\Formatters;

class DateTimeFormatter implements Formatter
{
    public function __construct(protected string $format = \DateTimeInterface::ATOM) {}

    public function format(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface ? $value->format($this->format) : $value;
    }
}
