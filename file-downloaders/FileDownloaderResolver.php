<?php

final class FileDownloaderResolver
{
    /** @var string IS_MPD_REGEX */
    private const IS_MPD_REGEX = '/\.mpd$/';

    /**
     * Get downloader base on parameters
     * @param string $url
     * @param string $playlistTitle
     * @param string|null $downloadsFolder
     * @return FileDownloader
     */
    public static function getDownloader(string $url, string $playlistTitle, ?string $downloadsFolder): FileDownloader
    {
        switch (true) {
            case self::isMPD($url):
                return new MPDFileDownloaderStrategy(
                    LoggerProducer::create(),
                    $playlistTitle,
                    $downloadsFolder,
                    FileConverterResolver::getConverter()
                );
            default:
                return new MP4FileDownloaderStrategy(
                    LoggerProducer::create(),
                    $playlistTitle,
                    $downloadsFolder
                );
        }
    }

    /**
     * Is the current source leading to mpd file?
     * @param string $source
     * @return bool
     */
    private static function isMPD(string $source): bool
    {
        return preg_match(self::IS_MPD_REGEX, $source);
    }
}
