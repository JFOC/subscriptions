<?php

declare(strict_types=1);

namespace Crumbls\Subscriptions\Enums;

enum Interval: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    public function addToDate(\Carbon\Carbon $date, int $count): \Carbon\Carbon
    {
        return match ($this) {
            self::Hour => $date->addHours($count),
            self::Day => $date->addDays($count),
            self::Week => $date->addWeeks($count),
            self::Month => $date->addMonths($count),
            self::Year => $date->addYears($count),
        };
    }
}
