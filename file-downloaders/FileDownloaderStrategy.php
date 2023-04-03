<?php

abstract class FileDownloaderStrategy implements FileDownloader
{
    /** @var Logger $logger */
    protected Logger $logger;

    /** @var string DOWNLOADS_FOLDER */
    protected const DOWNLOADS_FOLDER = 'downloads';

    /** @var string $downloadSessionFolder */
    protected string $downloadSessionFolder;

    /** @var string OUTPUT_MEDIA_TYPE */
    protected const OUTPUT_MEDIA_TYPE = 'output';

    /** @var string FILE_SAVED_TO_MSG */
    protected const FILE_SAVED_TO_MSG = '%s downloaded and saved as %s';

    /** @var string BLANK_VIDEO_SRC */
    private const BLANK_VIDEO_SRC = 'blank';

    /**
     * @param Logger $logger
     * @param string $playlistTitle
     * @param string|null $downloadSessionFolder
     */
    public function __construct(Logger $logger, string $playlistTitle, string $downloadSessionFolder = null)
    {
        $this->logger = $logger;
        $this->downloadSessionFolder = $downloadSessionFolder ?: $this->createFolderForThisDownloadSession($playlistTitle);
    }

    /**
     * Retrieve folder for this session folder
     * @return string
     */
    public function getFolderForThisDownloadSession(): string
    {
        return $this->downloadSessionFolder;
    }

    /**
     * If the video is private or erased
     * @param array $videoData
     * @return bool
     */
    protected function isBlankSrc(array $videoData): bool
    {
        return empty($videoData['options']['src']) || $videoData['options']['src'] === self::BLANK_VIDEO_SRC;
    }

    /**
     * Generate a sub-folder in downloads folder to store the files for current session
     * @return string
     */
    protected function createFolderForThisDownloadSession(string $playlistTitle = null): string
    {
        $timestamp = date('Y-m-d_H:i:s');
        $dirname = sprintf('%s/%s_%s', self::DOWNLOADS_FOLDER, $playlistTitle, $timestamp);
        if (!is_dir($dirname)) {
            mkdir($dirname);
        }

        return $dirname;
    }

    /**
     * Generate downloads output file name
     * @param string $outputDir
     * @param string $title
     * @return string
     */
    protected function generateDownloadsOutputFileName(string $outputDir, string $title): string
    {
        return sprintf('%s/%s.mp4', $outputDir, $title);
    }

    /**
     * Wrapper for $logger->log
     * @param string $template
     * @param array $args
     * @return void
     */
    protected function log(string $template, array $args = []): void
    {
        $message = $this->logger->generateMessage($template, $args);
        $this->logger->log($message);
    }

    /**
     * Abstract function saveToHDD
     * @param array $videoData
     * @return bool
     */
    abstract function saveToHDD(array $videoData): bool;
}
