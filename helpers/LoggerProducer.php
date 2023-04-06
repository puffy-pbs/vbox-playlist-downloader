<?php

final class LoggerProducer
{
    /**
     * Get logger
     * @return Logger
     */
    public static function create(): Logger
    {
        return new GenericLogger();
    }
}
