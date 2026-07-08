<?php

namespace TreptowLabs\Envelope\Tests;

use PHPUnit\Framework\TestCase;
use TreptowLabs\Envelope\None;
use TreptowLabs\Envelope\Some;

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

    public function testCanMapInternalValueToAnotherOption()
    {
        $value = Some::make('default');

        $value = $value->map(fn ($v) => $v.'2');
        $this->assertTrue($value->isSome());
        $this->assertEquals('default2', $value->unwrap());

        $value = $value->map(fn($v) => Some::make($v.'2'));
        $this->assertTrue($value->isSome());
        $this->assertEquals('default22', $value->unwrap());

        $value = $value->map(fn ($v) => None::make());
        $this->assertTrue($value->isNone());
    }
}
