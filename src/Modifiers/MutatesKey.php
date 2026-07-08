<?php

namespace TreptowLabs\Envelope\Modifiers;

use TreptowLabs\Envelope\Option;

interface MutatesKey
{
    /**
     * @param  Option<array-key>  $key
     * @return Option<array-key>
     */
    public function mutateKey(Option $key, mixed $value): Option;
}
