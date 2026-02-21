# Changelog

## v1.0.0 — 2026-02-20

### Breaking Changes (from rinvex/laravel-subscriptions)
- Namespace changed from `Rinvex\Subscriptions` to `Crumbls\Subscriptions`
- Config key changed from `rinvex.subscriptions` to `subscriptions`
- Removed `rinvex/laravel-support` dependency entirely
- Removed custom artisan commands (`rinvex:migrate`, `rinvex:publish`, `rinvex:rollback`) — use standard `migrate` / `vendor:publish`
- Removed model-level validation (ValidatingTrait) — use form requests instead

### Added
- `Interval` backed enum (`hour`, `day`, `week`, `month`, `year`) with `addToDate()` helper
- Grace period support — `onGracePeriod()` method and active check
- Lifecycle events: `SubscriptionCreated`, `SubscriptionCanceled`, `SubscriptionRenewed`, `SubscriptionPlanChanged`
- `subscriptions:prune` artisan command for cleaning up expired subscriptions
- `@property` docblocks on all models for IDE and static analysis support
- PHPStan (level 5) + Larastan — clean
- Pest test suite — 48 tests, 105 assertions (SQLite in-memory)

### Modernized
- Requires PHP 8.2+ and Laravel 11/12
- `casts()` method instead of `$casts` property (Laravel 11+ convention)
- Anonymous class migrations with `$table->id()` and `foreignId()->constrained()`
- Constructor promotion, typed properties, `static` return types
- Uses Spatie packages directly (`HasSlug`, `HasTranslations`, `SortableTrait`)
- Void return types on closures, modern PHPDoc generics
- Rector (Laravel Shift rules) applied for full Laravel 11 compliance
