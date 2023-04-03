<?php

class Delay
{
    /** @var int DEFAULT_MIN_SECONDS_DELAY */
    private const DEFAULT_MIN_SECONDS_DELAY = 4;

    /** @var int DEFAULT_MAX_SECONDS_DELAY */
    private const DEFAULT_MAX_SECONDS_DELAY = 10;

    /**
     * Generate random int
     * @return int
     */
    private static function generateRandomSec(): int
    {
        return mt_rand(self::DEFAULT_MIN_SECONDS_DELAY, self::DEFAULT_MAX_SECONDS_DELAY);
    }

    /**
     * Make it sleep
     * @return void
     */
    public static function wait(): void
    {
        sleep(self::generateRandomSec());
    }
}
