<?php

declare(strict_types=1);

namespace Crumbls\Subscriptions\Services;

use Carbon\Carbon;
use Crumbls\Subscriptions\Enums\Interval;

class Period
{
    protected Carbon $start;
    protected Carbon $end;
    protected Interval $interval;

    public function __construct(Interval|string $interval, protected int $count = 1, Carbon|string $start = '')
    {
        $this->interval = $interval instanceof Interval
            ? $interval
            : Interval::from($interval);
        $this->start = match (true) {
            $start instanceof Carbon => $start->copy(),
            $start === '' => Carbon::now(),
            default => new Carbon($start),
        };

        $this->end = $this->interval->addToDate($this->start->copy(), $this->count);
    }

    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    public function getEndDate(): Carbon
    {
        return $this->end;
    }

    public function getInterval(): Interval
    {
        return $this->interval;
    }

    public function getIntervalCount(): int
    {
        return $this->count;
    }
}
