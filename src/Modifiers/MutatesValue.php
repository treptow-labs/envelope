<?php

namespace TreptowLabs\Envelope\Modifiers;

use TreptowLabs\Envelope\Option;

interface MutatesValue
{
    /**
     * @param  Option<array-key>  $key
     */
    public function mutateValue(Option $key, mixed $value): mixed;
}
