<?php

namespace TreptowLabs\Envelope\Support;

use Closure;
use TreptowLabs\Envelope\Exceptions\UnresolvableValueException;
use TreptowLabs\Envelope\None;
use TreptowLabs\Envelope\Option;
use TreptowLabs\Envelope\Some;

class EnvelopeResolver
{
    public function __construct(protected array $data) {}

    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * @template TExpectedType
     *
     * @param  (Closure(mixed): TExpectedType)|null  $callback
     * @return ($callback is Closure ? Option<TExpectedType> : Option<mixed>)
     */
    public function get(string $key, ?Closure $callback = null): Option
    {
        if (array_key_exists($key, $this->data)) {
            $value = $this->data[$key];
            if ($callback !== null) {
                $value = $callback($value);
            }

            return Some::make($value);
        }

        return None::make();
    }

    /**
     * @return ($nullable is true ? Option<bool|null> : Option<bool>)
     */
    public function boolean(string $key, bool $nullable = true, bool $throw = false): Option
    {
        return $this->get($key, function ($value) use ($key, $throw, $nullable) {
            if (! is_null($value)) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            if ($nullable) {
                return null;
            }
            if ($throw) {
                throw new UnresolvableValueException($key, $value);
            }

            return false;
        });
    }

    public function booleanOrThrow(string $key, bool $nullable = false): Option
    {
        return $this->boolean($key, $nullable, true);
    }

    /**
     * @return ($nullable is true ? Option<string|null> : Option<string>)
     */
    public function string(string $key, bool $nullable = true, bool $throw = false): Option
    {
        return $this->get($key, function ($value) use ($key, $throw, $nullable) {
            if (! is_null($value)) {
                if (is_array($value)) {
                    return json_encode($value);
                }

                return (string) $value;
            }
            if ($nullable) {
                return null;
            }
            if ($throw) {
                throw new UnresolvableValueException($key, $value);
            }

            return '';
        });
    }

    public function stringOrThrow(string $key, bool $nullable = false): Option
    {
        return $this->string($key, $nullable, true);
    }

    /**
     * @return ($nullable is true ? Option<int|null> : Option<int>)
     */
    public function int(string $key, bool $nullable = true, bool $throw = false): Option
    {
        return $this->get($key, function ($value) use ($key, $nullable, $throw) {
            if (! is_null($value)) {
                return (int) ($value);
            }
            if ($nullable) {
                return null;
            }
            if ($throw) {
                throw new UnresolvableValueException($key, $value);
            }

            return 0;
        });
    }

    public function intOrThrow(string $key, bool $nullable = false): Option
    {
        return $this->int($key, $nullable, true);
    }

    public function integer(string $key, bool $nullable = true, bool $throw = false): Option
    {
        return $this->int($key, $nullable, $throw);
    }

    public function integerOrThrow(string $key, bool $nullable = false): Option
    {
        return $this->int($key, $nullable, true);
    }

    /**
     * @return ($nullable is true ? Option<float|null> : Option<float>)
     */
    public function float(string $key, bool $nullable = true, bool $throw = false): Option
    {
        return $this->get($key, function ($value) use ($key, $nullable, $throw) {
            if (! is_null($value)) {
                return (float) ($value);
            }
            if ($nullable) {
                return null;
            }
            if ($throw) {
                throw new UnresolvableValueException($key, $value);
            }

            return 0.0;
        });
    }

    public function floatOrThrow(string $key, bool $nullable = false): Option
    {
        return $this->float($key, $nullable, true);
    }

    /**
     * @template TEnum of \BackedEnum
     *
     * @param  class-string<TEnum>  $enumClass
     * @return ($nullable is true ? Option<TEnum|null> : Option<TEnum>)
     */
    public function enum(string $key, string $enumClass, bool $nullable = true): Option
    {
        return $this->get($key, function ($value) use ($key, $enumClass, $nullable) {
            if ($value !== null) {
                if ($value instanceof $enumClass) {
                    return $value;
                }

                $value = $enumClass::tryFrom($value);
            }

            if ($value !== null) {
                return $value;
            }

            if ($nullable) {
                return null;
            }

            throw new UnresolvableValueException($key, $value);
        });
    }

    /**
     * @return ($nullable is true ? Option<array|null> : Option<array>)
     */
    public function array(string $key, bool $nullable = true, bool $throw = false): Option
    {
        return $this->get($key, function ($value) use ($key, $nullable, $throw) {
            if (! is_null($value)) {
                return (array) ($value);
            }
            if ($nullable) {
                return null;
            }
            if ($throw) {
                throw new UnresolvableValueException($key, $value);
            }

            return [];
        });
    }

    public function arrayOrThrow(string $key, bool $nullable = false): Option
    {
        return $this->array($key, $nullable, true);
    }
}
