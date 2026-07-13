<?php
namespace TreptowLabs\Envelope\Exceptions;

class UnwrapException extends \RuntimeException {
    public function __construct()
    {
        parent::__construct('Attempted to call [unwrap] on a [None] value. Use [unwrapOr] instead.');
    }
}