<?php

namespace TreptowLabs\Envelope\Formatters;

/**
 * @template TInput
 * @template TOutput
 */
interface Formatter
{
    public function format(mixed $value): mixed;
}
