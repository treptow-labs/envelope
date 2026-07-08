<?php

namespace TreptowLabs\Envelope;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use TreptowLabs\Envelope\Contents\Contents;
use TreptowLabs\Envelope\Contents\Some;
use TreptowLabs\Envelope\Modifiers\MutatesKey;
use TreptowLabs\Envelope\Modifiers\MutatesValue;

abstract class Envelope implements Arrayable, Jsonable, JsonSerializable
{
    public function toArray(): array
    {
        $publicProperties = (new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC);

        $output = [];
        foreach ($publicProperties as $property) {
            $key = Some::make($property->getName());
            $value = $property->getValue($this);

            if ($value instanceof Contents) {
                if ($value->isNone()) {
                    continue;
                }
                $value = $value->unwrap();
            }

            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();
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
            $output[$key->unwrap()] = $value;
        }

        return $output;
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
