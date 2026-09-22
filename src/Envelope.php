<?php

namespace TreptowLabs\Envelope;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use TreptowLabs\Envelope\Modifiers\MutatesKey;
use TreptowLabs\Envelope\Modifiers\MutatesValue;

abstract class Envelope implements Arrayable, Jsonable, JsonSerializable
{
    /** @var array<class-string, array<int, array{name: string, attributes: array<int, object>}>> */
    protected static array $propertyMetadataCache = [];

    public function toArray(): array
    {
        $output = [];

        foreach (static::propertyMetadata() as $property) {
            $key = Some::make($property['name']);
            $value = $this->{$property['name']};

            if ($value instanceof Option) {
                if ($value->isNone()) {
                    continue;
                }
                $value = $value->unwrap();
            }

            foreach ($property['attributes'] as $instance) {
                if ($instance instanceof MutatesKey) {
                    $key = $instance->mutateKey($key, $value);
                }
                if ($instance instanceof MutatesValue) {
                    $value = $instance->mutateValue($key, $value);
                }
            }

            if ($key->isNone()) {
                continue;
            }
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }
            $output[$key->unwrap()] = $value;
        }

        return $output;
    }

    /** @return array<int, array{name: string, attributes: array<int, object>}> */
    protected static function propertyMetadata(): array
    {
        return self::$propertyMetadataCache[static::class] ??= array_map(
            static fn (\ReflectionProperty $property): array => [
                'name' => $property->getName(),
                'attributes' => array_map(
                    static fn (\ReflectionAttribute $attribute): object => $attribute->newInstance(),
                    $property->getAttributes()
                ),
            ],
            (new \ReflectionClass(static::class))->getProperties(\ReflectionProperty::IS_PUBLIC)
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
