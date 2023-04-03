<?php

final class FileConverterResolver
{
    /**
     * Get converter based on parameters
     * @return FileConverter
     */
    public static function getConverter(): FileConverter
    {
        switch (true) {
            default:
                return new DashVideoFileConverter();
        }
    }
}
