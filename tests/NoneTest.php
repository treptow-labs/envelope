<?php

namespace TreptowLabs\Envelope\Tests;

use PHPUnit\Framework\TestCase;
use TreptowLabs\Envelope\None;
use TreptowLabs\Envelope\Some;

class NoneTest extends TestCase
{
    public function testThrowsErrorForUnwrap()
    {
        $none = new None;

        $this->expectExceptionMessage('Attempted to call [unwrap] on a [None] value. Use [unwrapOr] instead.');
        $none->unwrap();
    }

    public function testReturnsValueForUnwrapOr()
    {
        $this->assertEquals('default', (new None)->unwrapOr('default'));
    }

    public function testReturnsValueOfCallableForUnwrapOr()
    {
        $this->assertEquals('default', (new None)->unwrapOr(fn () => 'default'));
    }

    public function testMapReturnsSelf()
    {
        $instance = new None;

        $this->assertTrue($instance->map(fn ($v) => Some::make('test'))->isNone());
    }
}
