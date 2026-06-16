# Componenta Clock

PSR-20 часы и фабрики даты/времени для детерминированной работы со временем.

## Установка

```bash
composer require componenta/clock
```

Пакет объявляет `Componenta\Clock\ConfigProvider` в `extra.componenta.config-providers`.
Если установлен `componenta/composer-plugin`, провайдер автоматически добавляется в сгенерированный список провайдеров.

## Требования

- PHP 8.4+
- `psr/clock`

## Связанные пакеты

| Пакет | Зачем нужен здесь |
|---|---|
| `psr/clock` | Определяет `ClockInterface`, на который может зависеть доменный и прикладной код. |
| `componenta/di` | Регистрирует `ClockInterface` и `DateTimeFactoryInterface` через `ConfigProvider`. |
| `componenta/cqrs` | Команды и обработчики используют часы для детерминированного времени вместо прямого `new DateTimeImmutable()`. |
| `componenta/session` | Может использовать время для TTL, активности и истечения сессий. |

## Что предоставляет пакет

- `Clock`: минимальные UTC-часы PSR-20.
- `DateTimeFactoryInterface`: PSR-20 clock плюс методы создания дат.
- `DateTimeFactory`: фабрика для продакшена с поддержкой timezone.
- `FrozenClock`: детерминированные часы и фабрика для тестов.
- Типизированные исключения для невалидных timezone и ошибок парсинга даты.

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

Сконфигурированный timezone доступен как read-only property:

```php
$clock->timezone->getName();
```

Смена timezone выполняется иммутабельно:

```php
$utc = $clock->withTimezone('UTC');
```

`withTimezone()` возвращает тот же инстанс, если запрошенный timezone уже активен.

## FrozenClock

Используйте `FrozenClock` в тестах и детерминированных workflow.

```php
use Componenta\Clock\FrozenClock;

$clock = new FrozenClock('2026-06-07 12:00:00', 'UTC');

$clock->now();      // fixed point in time
$clock->advance('+1 day');
$clock->freeze('2026-06-10 09:00:00');
```

## DI-регистрация

`ConfigProvider` регистрирует:

- фабрику для `DateTimeFactory::class`
- alias `Psr\Clock\ClockInterface` на `DateTimeFactoryInterface`
- alias `DateTimeFactoryInterface` на `DateTimeFactory`
