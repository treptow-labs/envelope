<?php

namespace TreptowLabs\Envelope\Attributes;

use TreptowLabs\Envelope\Contents\Contents;
use TreptowLabs\Envelope\Contents\None;
use TreptowLabs\Envelope\Modifiers\MutatesKey;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Omit implements MutatesKey
{
    public function mutateKey(Contents $key, mixed $value): Contents
    {
        return None::make();
    }
}
