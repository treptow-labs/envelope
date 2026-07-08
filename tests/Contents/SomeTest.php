<?php

namespace TreptowLabs\Envelope\Tests\Contents;

use PHPUnit\Framework\TestCase;
use TreptowLabs\Envelope\Contents\Some;

class SomeTest extends TestCase
{
    public function testReturnsInternalValueFromUnwrap()
    {
        $this->assertEquals('default', Some::make('default')->unwrap());
    }

    public function testReturnsInternalValueForUnwrapOr()
    {
        $this->assertEquals('default', Some::make('default')->unwrapOr('not default'));
    }

    public function testReturnsInternalValueForUnwrapOrWithCallable()
    {
        $this->assertEquals('default', Some::make('default')->unwrapOr(fn () => 'not default'));
    }
}
