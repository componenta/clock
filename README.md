# Componenta Clock

PSR-20 clock and datetime factory abstractions for deterministic time handling.

## Installation

```bash
composer require componenta/clock
```

The package declares `Componenta\Clock\ConfigProvider` in `extra.componenta.config-providers`.
When `componenta/composer-plugin` is installed, the provider is added to the generated provider list automatically.

## Requirements

- PHP 8.4+
- `psr/clock`

## Related Packages

| Package | Why it matters here |
|---|---|
| `psr/clock` | Defines `ClockInterface` for domain and application code. |
| `componenta/di` | Registers `ClockInterface` and `DateTimeFactoryInterface` through `ConfigProvider`. |
| `componenta/cqrs` | Commands and handlers can depend on clocks instead of constructing time directly. |
| `componenta/session` | Can use time for TTL, activity, and expiration logic. |

## What It Provides

- `Clock`: minimal UTC PSR-20 clock.
- `DateTimeFactoryInterface`: PSR-20 clock plus date creation methods.
- `DateTimeFactory`: production datetime factory with timezone support.
- `FrozenClock`: deterministic test clock and factory.
- Typed exceptions for invalid timezones and datetime parsing failures.

## DateTimeFactory

```php
use Componenta\Clock\DateTimeFactory;

$clock = new DateTimeFactory('Europe/Kaliningrad');

$now = $clock->now();
$today = $clock->today();
$fromTimestamp = $clock->fromTimestamp(1_700_000_000);
$parsed = $clock->parse('2026-06-07 12:00:00');
$fromFormat = $clock->fromFormat('Y-m-d', '2026-06-07');
```

The configured timezone is exposed as a read-only property:

```php
$clock->timezone->getName();
```

Change timezone immutably:

```php
$utc = $clock->withTimezone('UTC');
```

`withTimezone()` returns the same instance when the requested timezone is already active.

## FrozenClock

Use `FrozenClock` in tests or deterministic workflows.

```php
use Componenta\Clock\FrozenClock;

$clock = new FrozenClock('2026-06-07 12:00:00', 'UTC');

$clock->now();      // fixed point in time
$clock->advance('+1 day');
$clock->freeze('2026-06-10 09:00:00');
```

## DI Registration

`ConfigProvider` registers:

- `DateTimeFactory::class` factory
- `Psr\Clock\ClockInterface` alias to `DateTimeFactoryInterface`
- `DateTimeFactoryInterface` alias to `DateTimeFactory`
