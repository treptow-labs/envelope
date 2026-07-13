# Envelope

Envelope is a small PHP library for building DTOs that know the difference between **"this field was not provided"** and **"this field was explicitly set to `null`."**

It's built around a `Option` / `Some` / `None` type (similar to Rust's `Option<T>` or Java's `Optional<T>`), and an `Envelope` base class that turns "not provided" properties into fields that are automatically omitted from your DTO's array/JSON output.

## The problem

`null` is overloaded. Say you have a `PATCH /users/{id}` endpoint backed by a DTO like this:

```php
class UpdateUserDto
{
    public ?string $name;
    public ?string $bio;
}
```

If a client sends `{"name": "Jane"}`, how do you know they *didn't* mean to touch `bio` — versus a client sending `{"name": "Jane", "bio": null}`, who is explicitly asking you to clear `bio`?

Once a field is nullable, you can no longer use `null` as a sentinel for "not provided," because `null` is also a legitimate value. The usual workarounds — checking `array_key_exists` everywhere, sprinkling `isset()` checks through your update logic, or maintaining a parallel list of "dirty" fields — get messy fast, especially once a DTO has more than a couple of optional fields.

Envelope solves this by giving every property two possible states instead of two:

- **`Some($value)`** — the field was provided, and here's the value (which itself might be `null`)
- **`None`** — the field was not provided at all

Your DTO stays a normal, typed PHP class, but `toArray()` / `toJson()` will only ever include the fields that were actually provided — making it safe to feed straight into `Model::update()`, `Model::fill()`, or any other partial-update code path.

## Installation

```bash
composer require treptow-labs/envelope
```

## `Option`, `Some`, and `None`

`Option` is an abstract class with two concrete implementations:

- `Some` wraps a present value (which may itself be `null`)
- `None` represents the absence of a value

```php
use TreptowLabs\Envelope\Option;
use TreptowLabs\Envelope\Some;
use TreptowLabs\Envelope\None;

$present = Some::make('Jane');
$absent = None::make();

$present->isSome(); // true
$absent->isNone();  // true

$present->unwrap();      // 'Jane'
$absent->unwrap();       // throws \RuntimeException, None has nothing to unwrap
$absent->unwrapOr('N/A'); // 'N/A'
$absent->unwrapOr(fn () => 'N/A'); // 'N/A', callables are also accepted

$present->map(fn ($name) => strtoupper($name)); // Some('JANE')
$absent->map(fn ($name) => strtoupper($name));  // still None, map is a no-op on None
```

You can also build an `Option` from a raw value, optionally providing a sentinel that counts as "none":

```php
Option::from('Jane');       // Some('Jane')
Option::from(null);         // None (default sentinel is null)
Option::from(-1, -1);       // None ($value matches the given sentinel)
```

## Building a DTO with `Envelope`

Extend `Envelope` and type each property as an `Option`. Calling `toArray()` (or `toJson()`) will unwrap every `Some` and **skip every `None`** automatically:

```php
use TreptowLabs\Envelope\Envelope;
use TreptowLabs\Envelope\Option;

class UpdateUserDto extends Envelope
{
    public function __construct(
        public Option $name = new None(),
        public Option $bio = new None(),
        public Option $isActive = new None(),
    ) {}
}

$dto = new UpdateUserDto(
    name: Some::make('Jane'),
    isActive: Some::make(null), // explicitly provided, and explicitly null
);

$dto->toArray();
// ['name' => 'Jane', 'isActive' => null]
// 'bio' is completely absent from the array, it was never touched
```

Notice that `isActive` is present with a value of `null` (the "explicitly cleared" case) while `bio` doesn't appear at all, because it was never provided.

## Populating a DTO from request data: `EnvelopeResolver`

`EnvelopeResolver` wraps a raw array (e.g. `$request->validated()`) and gives you typed accessors that return `Some` if the key was present in the array, `None` if it wasn't:

```php
use TreptowLabs\Envelope\Support\EnvelopeResolver;

$resolver = EnvelopeResolver::make($request->validated());

$dto = new UpdateUserDto(
    name: $resolver->string('name'),
    bio: $resolver->string('bio'),
    isActive: $resolver->boolean('isActive'),
);
```

Available accessors, all following the same pattern:

| Method | Returns                                                   |
|---|-----------------------------------------------------------|
| `get(string $key, ?Closure $callback = null)` | Raw value, optionally transformed by `$callback`          |
| `string(string $key, bool $nullable = true, bool $throw = false)` | Cast to `string`                                          |
| `int(string $key, ...)` / `integer(...)` | Cast to `int`                                             |
| `float(string $key, ...)` | Cast to `float`                                           |
| `boolean(string $key, ...)` | Cast via `FILTER_VALIDATE_BOOLEAN`                        |
| `array(string $key, ...)` | Cast to `array`                                           |
| `enum(string $key, string $enumClass, bool $nullable = true)` | An instance of `$enumClass`,  via `$enumClass::tryFrom()` |

Each of these:

- Returns `None` if the key isn't present in the data at all.
- Returns `Some(null)` if the key is present but its value is `null` and `$nullable` is `true` (the default).
- Returns `Some($castValue)` if the key is present with a non-null value.
- If the key is present, the value is `null`, and `$nullable` is `false`: either returns a type-appropriate empty value (`''`, `0`, `0.0`, `false`, `[]`) or throws `UnresolvableValueException`, depending on `$throw`.

Every accessor has an `...OrThrow` sibling (`stringOrThrow`, `intOrThrow`/`integerOrThrow`, `floatOrThrow`, `booleanOrThrow`, `arrayOrThrow`) that's shorthand for calling it with `nullable: false, throw: true`; useful for fields that were provided but must not resolve to null:

```php
$resolver->stringOrThrow('email');
// same as: $resolver->string('email', nullable: false, throw: true)
```

## Shaping the output: attributes

Three attributes let you control how a property is serialized without touching your update logic.

### `#[MapsTo]` - rename the output key

```php
use TreptowLabs\Envelope\Attributes\MapsTo;

class UpdateUserDto extends Envelope
{
    public function __construct(
        #[MapsTo('is_active')]
        public Option $isActive,
    ) {}
}
```

### `#[Omit]` - always exclude a property from toArray output

Useful for properties you need on the DTO for internal logic but never want serialized, regardless of whether they're `Some` or `None`:

```php
use TreptowLabs\Envelope\Attributes\Omit;

class UpdateUserDto extends Envelope
{
    public function __construct(
        public Option $name,
        #[Omit]
        public Option $internalNotes,
    ) {}
}
```

### `#[FormatUsing]` - transform the value before it's output

Pass a `Formatter` class (and any constructor arguments it needs):

```php
use TreptowLabs\Envelope\Attributes\FormatUsing;
use TreptowLabs\Envelope\Formatters\DateTimeFormatter;

class UpdateEventDto extends Envelope
{
    public function __construct(
        #[FormatUsing(DateTimeFormatter::class, \DateTimeInterface::ATOM)]
        public Option $startsAt,
    ) {}
}
```

Envelope ships with two formatters out of the box:

- **`DateTimeFormatter`** - formats a `DateTimeInterface` value to a string (defaults to `DateTimeInterface::ATOM`); non-`DateTimeInterface` values pass through unchanged.
- **`StringFormatter`** - safely casts a value to `string`, passing `null` through as `null` and calling `__toString()` on `Stringable` objects.

You can write your own by implementing the `Formatter` interface:

```php
use TreptowLabs\Envelope\Formatters\Formatter;

class UppercaseFormatter implements Formatter
{
    public function format(mixed $value): mixed
    {
        return is_string($value) ? strtoupper($value) : $value;
    }
}
```

## Putting it all together: a PATCH endpoint

```php
use TreptowLabs\Envelope\Attributes\MapsTo;
use TreptowLabs\Envelope\Attributes\FormatUsing;
use TreptowLabs\Envelope\Envelope;
use TreptowLabs\Envelope\Formatters\DateTimeFormatter;
use TreptowLabs\Envelope\Option;
use TreptowLabs\Envelope\Support\EnvelopeResolver;

class UpdateUserDto extends Envelope
{
    public function __construct(
        public Option $name,
        #[MapsTo('is_active')]
        public Option $isActive,
        #[FormatUsing(DateTimeFormatter::class)]
        public Option $verifiedAt,
    ) {}

    public static function fromRequest(array $data): self
    {
        $resolver = EnvelopeResolver::make($data);

        return new self(
            name: $resolver->string('name'),
            isActive: $resolver->boolean('is_active'),
            verifiedAt: $resolver->get('verified_at', fn ($v) => $v ? new \DateTimeImmutable($v) : null),
        );
    }
}

// In your controller:
public function update(Request $request, User $user)
{
    $dto = UpdateUserDto::fromRequest($request->all());

    $user->update($dto->toArray());

    return $user;
}

// Example requests:
PATCH /users/123
{"name": "Jane"}

- Only `name` is updated; `is_active` and `verified_at` are untouched

PATCH /users/123
{"verified_at": null}

- `verified_at` is set to `null`; `name` and `is_active` are untouched
```