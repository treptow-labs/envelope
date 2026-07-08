<?php

namespace TreptowLabs\Envelope\Attributes;

use TreptowLabs\Envelope\Formatters\Formatter;
use TreptowLabs\Envelope\Modifiers\MutatesValue;
use TreptowLabs\Envelope\Option;

/**
 * @template TInput
 * @template TOutput
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FormatUsing implements MutatesValue
{
    /** @var array<array-key, mixed> */
    protected readonly array $arguments;

    /**
     * @param  class-string<Formatter<TInput, TOutput>>  $formatUsing
     */
    public function __construct(protected readonly string $formatUsing, mixed ...$arguments)
    {
        if (! is_a($this->formatUsing, Formatter::class, true)) {
            throw new \InvalidArgumentException('The provided formatter must implement '.Formatter::class);
        }

        $this->arguments = $arguments;
    }

    public function mutateValue(Option $key, mixed $value): mixed
    {
        return $this->make()->format($value);
    }

    /**
     * @return Formatter<TInput, TOutput>
     */
    protected function make(): Formatter
    {
        return new ($this->formatUsing)(...$this->arguments);
    }
}
