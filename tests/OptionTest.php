<?php

namespace TreptowLabs\Envelope\Tests;

use PHPUnit\Framework\TestCase;
use TreptowLabs\Envelope\None;
use TreptowLabs\Envelope\Option;
use TreptowLabs\Envelope\Some;

class OptionTest extends TestCase
{
    public function testCanCreateNewInstanceFromValue()
    {
        $contents = Option::from(null);
        $this->assertInstanceOf(None::class, $contents);
        $this->assertFalse($contents->isSome());
        $this->assertTrue($contents->isNone());

        $contents = Option::from(null, '');
        $this->assertInstanceOf(Some::class, $contents);
        $this->assertTrue($contents->isSome());
        $this->assertFalse($contents->isNone());

        $contents = Option::from('test');
        $this->assertInstanceOf(Some::class, $contents);
        $this->assertTrue($contents->isSome());
        $this->assertFalse($contents->isNone());
    }
}
