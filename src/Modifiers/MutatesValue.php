<?php

namespace TreptowLabs\Envelope\Modifiers;

use TreptowLabs\Envelope\Contents\Contents;

interface MutatesValue
{
    /**
     * @param  Contents<array-key>  $key
     */
    public function mutateValue(Contents $key, mixed $value): mixed;
}
