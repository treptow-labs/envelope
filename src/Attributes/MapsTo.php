<?php

namespace TreptowLabs\Envelope\Attributes;

use TreptowLabs\Envelope\Modifiers\MutatesKey;
use TreptowLabs\Envelope\Option;
use TreptowLabs\Envelope\Some;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class MapsTo implements MutatesKey
{
    public function __construct(protected string $value) {}

    public function mutateKey(Option $key, mixed $value): Option
    {
        return Some::make($this->value);
    }
}
