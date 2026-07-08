<?php

namespace TreptowLabs\Envelope;

/**
 * @extends Option<null>
 */
class None extends Option
{
    public function unwrap()
    {
        throw new \RuntimeException('Attempted to call [unwrap] on a [None] value. Use [unwrapOr] instead.');
    }

    public function unwrapOr(mixed $callable): mixed
    {
        return is_callable($callable) ? call_user_func($callable) : $callable;
    }

    public static function make(): None
    {
        return new None;
    }
}
