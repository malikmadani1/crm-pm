<?php

namespace App\Support;

class Duration
{
    public static function fromSeconds(int $seconds): string
    {
        $seconds = max(0, $seconds);

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            $parts = [self::unit($hours, 'hour', 'hours')];

            if ($minutes > 0) {
                $parts[] = self::unit($minutes, 'minute', 'minutes');
            }

            return implode(' '.__('and').' ', $parts);
        }

        if ($minutes > 0) {
            return self::unit($minutes, 'minute', 'minutes').' '.__('and').' '.self::unit($remainingSeconds, 'second', 'seconds');
        }

        return self::unit($remainingSeconds, 'second', 'seconds');
    }

    public static function fromMinutes(int|float|null $minutes): string
    {
        return self::fromSeconds((int) round(((float) ($minutes ?? 0)) * 60));
    }

    public static function fromHours(int|float|null $hours): string
    {
        return self::fromSeconds((int) round(((float) ($hours ?? 0)) * 3600));
    }

    private static function unit(int $value, string $singular, string $plural): string
    {
        return $value.' '.($value === 1 ? __($singular) : __($plural));
    }
}
