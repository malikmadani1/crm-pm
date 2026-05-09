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
            $parts = ["{$hours} ساعة"];

            if ($minutes > 0) {
                $parts[] = "{$minutes} دقيقة";
            }

            return implode(' و ', $parts);
        }

        if ($minutes > 0) {
            return "{$minutes} دقيقة و {$remainingSeconds} ثانية";
        }

        return "{$remainingSeconds} ثانية";
    }

    public static function fromMinutes(int|float|null $minutes): string
    {
        return self::fromSeconds((int) round(((float) ($minutes ?? 0)) * 60));
    }

    public static function fromHours(int|float|null $hours): string
    {
        return self::fromSeconds((int) round(((float) ($hours ?? 0)) * 3600));
    }
}
