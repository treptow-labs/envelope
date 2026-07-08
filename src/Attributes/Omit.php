<?php

namespace TreptowLabs\Envelope\Attributes;

use TreptowLabs\Envelope\Modifiers\MutatesKey;
use TreptowLabs\Envelope\None;
use TreptowLabs\Envelope\Option;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Omit implements MutatesKey
{
    public function mutateKey(Option $key, mixed $value): Option
    {
        return None::make();
    }
}
