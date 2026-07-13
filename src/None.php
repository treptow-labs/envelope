<?php

namespace TreptowLabs\Envelope;

use TreptowLabs\Envelope\Exceptions\UnwrapException;

/**
 * @extends Option<null>
 */
class None extends Option
{
    public function unwrap()
    {
        throw new UnwrapException();
    }

    public function unwrapOr(mixed $callable): mixed
    {
        return is_callable($callable) ? call_user_func($callable) : $callable;
    }

    public function map(callable $callable): Option
    {
        return $this;
    }

    public static function make(): None
    {
        return new None;
    }
}
