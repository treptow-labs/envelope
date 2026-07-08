<?php

namespace TreptowLabs\Envelope\Tests\Support;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use TreptowLabs\Envelope\Contents\None;
use TreptowLabs\Envelope\Contents\Some;
use TreptowLabs\Envelope\Support\EnvelopeResolver;

class EnvelopeResolverTest extends TestCase
{
    public function testReturnsNoneIfKeyIsNotPresent()
    {
        $resolver = EnvelopeResolver::make([]);

        $this->assertInstanceOf(None::class, $resolver->get('key'));
    }

    public function testReturnsSomeIfKeyIsPresent()
    {
        $resolver = EnvelopeResolver::make([
            'key' => 'value',
            'another_key' => null,
        ]);

        $this->assertInstanceOf(Some::class, $resolver->get('key'));
        $this->assertEquals('value', $resolver->get('key')->unwrap());

        $this->assertInstanceOf(Some::class, $resolver->get('another_key'));
        $this->assertNull($resolver->get('another_key')->unwrap());
    }

    #[TestWith(['123', 123])]
    #[TestWith([123, 123])]
    #[TestWith([null, null])]
    public function testCanResolveInteger(mixed $value, mixed $expected)
    {
        $resolver = EnvelopeResolver::make(['key' => $value]);

        $this->assertSame($expected, $resolver->int('key')->unwrap());
    }

    #[TestWith(['123', 123])]
    #[TestWith([123, 123])]
    #[TestWith([null, 0])]
    public function testCanResolveNullInteger(mixed $value, mixed $expected)
    {
        $resolver = EnvelopeResolver::make(['key' => $value]);

        $this->assertSame($expected, $resolver->int('key', false)->unwrap());
    }

    public function testCanResolveNullIntegerWithThrow()
    {
        $resolver = EnvelopeResolver::make(['key' => null]);

        $this->expectExceptionMessage('Unresolvable value provided for [key]');
        $resolver->int('key', false, true);
    }

    #[TestWith(['123', '123'])]
    #[TestWith([123, '123'])]
    #[TestWith([null, null])]
    #[TestWith([[], '[]'])]
    #[TestWith([['key' => 'value'], '{"key":"value"}'])]
    #[TestWith(['', ''])]
    #[TestWith([123.00, '123'])]
    public function testCanResolveString(mixed $value, mixed $expected)
    {
        $resolver = EnvelopeResolver::make(['key' => $value]);
        $this->assertSame($expected, $resolver->string('key')->unwrap());
    }

    public function testCanResolveStringWithNotNull()
    {
        $resolver = EnvelopeResolver::make(['key' => null]);

        $this->assertSame('', $resolver->string('key', false)->unwrap());
    }

    public function testCanThrowOnNullString()
    {
        $resolver = EnvelopeResolver::make(['key' => null]);
        $this->expectExceptionMessage('Unresolvable value provided for [key]');
        $resolver->string('key', false, true);
    }

    #[TestWith([TestEnum::A, TestEnum::A])]
    #[TestWith(['a', TestEnum::A])]
    #[TestWith(['b', TestEnum::B])]
    #[TestWith(['z', null])]
    #[TestWith(['', null])]
    #[TestWith([null, null])]
    public function testCanResolveEnum(mixed $value, mixed $expected){
        $resolver = EnvelopeResolver::make(['key' => $value]);

        $this->assertSame($expected, $resolver->enum('key', TestEnum::class)->unwrap());
    }

    #[TestWith(['z'])]
    #[TestWith(['A'])]
    #[TestWith(['AB'])]
    public function testThrowsErrorWhenResolvingEnumWithoutNullable(mixed $value){
        $resolver = EnvelopeResolver::make(['key' => $value]);

        $this->expectExceptionMessage('Unresolvable value provided for [key]');
        $resolver->enum('key', TestEnum::class, false);
    }

    #[TestWith(['0.01', 0.01])]
    #[TestWith([0.01, 0.01])]
    #[TestWith([1, 1.0])]
    #[TestWith(['1', 1.0])]
    #[TestWith([null, null])]
    #[TestWith(['', 0.0])]
    public function testCanResolveFloat(mixed $value, mixed $expected){
        $resolver = EnvelopeResolver::make(['key' => $value]);
        $this->assertSame($expected, $resolver->float('key')->unwrap());
    }

    #[TestWith(['', 0.0])]
    #[TestWith([null, 0.0])]
    public function testCanResolveFloatWithNotNull(mixed $value, mixed $expected){
        $resolver = EnvelopeResolver::make(['key' => $value]);
        $this->assertSame($expected, $resolver->float('key', false)->unwrap());
    }

    public function testThrowsErrorWhenResolvingFloatWithoutNullable(){
        $resolver = EnvelopeResolver::make(['key' => null]);
        $this->expectExceptionMessage('Unresolvable value provided for [key]');
        $resolver->float('key', false, true);
    }
}

enum TestEnum: string {
    case A = 'a';
    case B = 'b';
}
