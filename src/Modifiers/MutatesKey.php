<?php

namespace TreptowLabs\Envelope\Modifiers;

use TreptowLabs\Envelope\Contents\Contents;

interface MutatesKey
{
    /**
     * @param  Contents<array-key>  $key
     * @return Contents<array-key>
     */
    public function mutateKey(Contents $key, mixed $value): Contents;
}
