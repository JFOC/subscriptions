<?php

use Carbon\Carbon;
use Crumbls\Subscriptions\Enums\Interval;
use Crumbls\Subscriptions\Services\Period;

it('creates a period with default start', function (): void {
    Carbon::setTestNow('2026-03-01 12:00:00');

    $period = new Period('month', 1);

    expect($period->getStartDate()->toDateTimeString())->toBe('2026-03-01 12:00:00');
    expect($period->getEndDate()->toDateTimeString())->toBe('2026-04-01 12:00:00');
    expect($period->getInterval())->toBe(Interval::Month);
    expect($period->getIntervalCount())->toBe(1);

    Carbon::setTestNow();
});

it('creates a period with a specific start date', function (): void {
    $start = Carbon::parse('2026-06-15');
    $period = new Period(Interval::Week, 2, $start);

    expect($period->getStartDate()->toDateString())->toBe('2026-06-15');
    expect($period->getEndDate()->toDateString())->toBe('2026-06-29');
});

it('accepts string interval', function (): void {
    $period = new Period('day', 30, Carbon::parse('2026-01-01'));

    expect($period->getEndDate()->toDateString())->toBe('2026-01-31');
});
