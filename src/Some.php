<?php

namespace TreptowLabs\Envelope;

/**
 * @template TSomeValue
 *
 * @extends  Option<TSomeValue>
 */
class Some extends Option
{
    /**
     * @param  TSomeValue  $value
     */
    public function __construct(
        protected readonly mixed $value
    ) {}

    /**
     * @return TSomeValue
     */
    public function unwrap()
    {
        return $this->value;
    }

    /**
     * @return TSomeValue
     */
    public function unwrapOr(mixed $callable): mixed
    {
        return $this->value;
    }

    /**
     * @template TValue
     *
     * @param  TValue  $value
     * @return Some<TValue>
     */
    public static function make(mixed $value): Some
    {
        return new self($value);
    }
}
