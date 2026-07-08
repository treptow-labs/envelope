<?php

namespace TreptowLabs\Envelope\Tests\Contents;

use PHPUnit\Framework\TestCase;
use TreptowLabs\Envelope\Contents\Contents;
use TreptowLabs\Envelope\Contents\None;
use TreptowLabs\Envelope\Contents\Some;

class ContentsTest extends TestCase
{
    public function testCanCreateNewInstanceFromValue()
    {
        $contents = Contents::from(null);
        $this->assertInstanceOf(None::class, $contents);
        $this->assertFalse($contents->isSome());
        $this->assertTrue($contents->isNone());

        $contents = Contents::from(null, '');
        $this->assertInstanceOf(Some::class, $contents);
        $this->assertTrue($contents->isSome());
        $this->assertFalse($contents->isNone());

        $contents = Contents::from('test');
        $this->assertInstanceOf(Some::class, $contents);
        $this->assertTrue($contents->isSome());
        $this->assertFalse($contents->isNone());
    }
}
