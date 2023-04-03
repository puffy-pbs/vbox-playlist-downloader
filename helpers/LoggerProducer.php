<?php

final class LoggerProducer
{
    public static function create(): Logger
    {
        // ... some more will come eventually
        return new GenericLogger();
    }
}
