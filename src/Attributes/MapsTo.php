<?php

namespace TreptowLabs\Envelope\Attributes;

use TreptowLabs\Envelope\Contents\Contents;
use TreptowLabs\Envelope\Contents\Some;
use TreptowLabs\Envelope\Modifiers\MutatesKey;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class MapsTo implements MutatesKey
{
    public function __construct(protected string $value) {}

    public function mutateKey(Contents $key, mixed $value): Contents
    {
        return Some::make($this->value);
    }
}
