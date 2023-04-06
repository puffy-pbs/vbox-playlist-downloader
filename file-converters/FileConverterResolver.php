<?php

final class FileConverterResolver
{
    /**
     * Get converter
     * @return FileConverter
     */
    public static function getConverter(): FileConverter
    {
        return new DashVideoFileConverter();
    }
}
