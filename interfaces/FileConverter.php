<?php

interface FileConverter
{
    public function convert(DashVideoConvertableDto $dto): bool;
}
