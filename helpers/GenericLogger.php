<?php

class GenericLogger implements Logger
{
    /**
     * Log the $msg
     * @param string $msg
     * @return void
     */
    public function log(string $msg): void
    {
        echo($msg . PHP_EOL);
    }

    /**
     * Generate message based on a template + array of arguments
     * @param string $template
     * @param array $args
     * @return string
     */
    public function generateMessage(string $template, array $args): string
    {
        return sprintf($template, ...$args);
    }
}
