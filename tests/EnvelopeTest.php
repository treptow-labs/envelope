<?php

namespace TreptowLabs\Envelope\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use TreptowLabs\Envelope\Attributes\FormatUsing;
use TreptowLabs\Envelope\Attributes\MapsTo;
use TreptowLabs\Envelope\Attributes\Omit;
use TreptowLabs\Envelope\Envelope;
use TreptowLabs\Envelope\Formatters\DateTimeFormatter;
use TreptowLabs\Envelope\Formatters\StringFormatter;
use TreptowLabs\Envelope\None;
use TreptowLabs\Envelope\Option;
use TreptowLabs\Envelope\Some;

class EnvelopeTest extends TestCase
{
    public function testToArrayOnlyUsesPublicProperties()
    {
        $envelope = new class(Some::make('value'), Some::make('value2'), Some::make('value3'), 'value4') extends Envelope
        {
            public function __construct(
                public readonly Option $property,
                protected readonly Option $property2,
                private readonly Option $property3,
                public readonly string $property4
            ) {}
        };

        $this->assertEquals([
            'property' => 'value',
            'property4' => 'value4',
        ], $envelope->toArray());
    }

    public function testToArrayExcludesNoneValues()
    {
        $envelope = new class(Some::make('value'), property3: Some::make('value2')) extends Envelope
        {
            public function __construct(
                public readonly Option $property,
                public readonly Option $property2 = new None,
                public readonly Option $property3 = new None,
            ) {}
        };

        $this->assertEquals([
            'property' => 'value',
            'property3' => 'value2',
        ], $envelope->toArray());
    }

    public function testToArrayCallsOnMutatesKeyAttributes()
    {
        $envelope = new class(Some::make('value'), property3: Some::make('value2')) extends Envelope
        {
            public function __construct(
                #[MapsTo('new_property')]
                public readonly Option $property,
                public readonly Option $property2 = new None,
                #[Omit]
                public readonly Option $property3 = new None,
            ) {}
        };

        $this->assertEquals(['new_property' => 'value'], $envelope->toArray());
    }

    public function testToArrayCallsOnMutatesValueAttributes()
    {
        $envelope = new class(Some::make('value'), Some::make('value2'), Some::make(new DateTime)) extends Envelope
        {
            public function __construct(
                #[FormatUsing(StringFormatter::class)]
                public readonly Option $property,
                #[FormatUsing(DateTimeFormatter::class)]
                public readonly Option $property2 = new None,
                #[FormatUsing(DateTimeFormatter::class, 'Y-m-d')]
                public readonly Option $property3 = new None,
            ) {}
        };

        $this->assertEquals(['property' => 'value', 'property2' => 'value2', 'property3' => date('Y-m-d')], $envelope->toArray());
    }

    public function testToArrayMultipleAttributes()
    {
        $envelope = new class(Some::make('value'), property3: Some::make(new DateTime)) extends Envelope
        {
            public function __construct(
                #[FormatUsing(StringFormatter::class)]
                #[MapsTo('new_property')]
                public readonly Option $property,
                public readonly Option $property2 = new None,
                #[FormatUsing(DateTimeFormatter::class, 'Y-m-d')]
                #[MapsTo('property')]
                public readonly Option $property3 = new None,
            ) {}
        };

        $this->assertEquals(['property' => date('Y-m-d'), 'new_property' => 'value'], $envelope->toArray());
    }
}
