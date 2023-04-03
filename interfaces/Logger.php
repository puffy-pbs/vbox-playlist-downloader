<?php

interface Logger
{
    public function log(string $msg): void;

    public function generateMessage(string $template, array $args): string;
}
