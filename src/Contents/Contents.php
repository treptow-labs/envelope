<?php

namespace TreptowLabs\Envelope\Contents;

/**
 * @template-covariant TContents
 */
abstract class Contents
{
    /**
     * @return TContents
     *
     * @throws \RuntimeException
     */
    abstract public function unwrap();

    /**
     * @template TCallable
     *
     * @param  (callable(): TCallable)|TCallable  $callable
     * @return TContents|TCallable
     */
    abstract public function unwrapOr(mixed $callable);

    /**
     * @phpstan-assert-if-true Some<TContents> $this
     */
    public function isSome(): bool
    {
        return $this instanceof Some;
    }

    /**
     * @phpstan-assert-if-true None $this
     */
    public function isNone(): bool
    {
        return $this instanceof None;
    }

    /**
     * @template TInputValue
     * @template TNoneValue
     *
     * @param  TInputValue  $value
     * @param  TNoneValue  $noneValue
     * @return (TInputValue is TNoneValue ? None : Some<TInputValue>)
     */
    public static function from(mixed $value, mixed $noneValue = null): self
    {
        if ($value === $noneValue) {
            return None::make();
        }

        return Some::make($value);
    }
}
