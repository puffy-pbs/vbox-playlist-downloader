<?php

class MPDFileDownloaderStrategy extends FileDownloaderStrategy
{
    /** @var string CORRUPTED_DASH_VIDEO_DATA_MSG */
    private const CORRUPTED_DASH_VIDEO_DATA_MSG = 'Corrupted dash-video data for video with title %s';

    /** @var string CORRUPTED_MEDIA_MSG */
    private const CORRUPTED_MEDIA_MSG = 'Corrupted %s for video with title %s';

    /** @var string VIDEO_CAN_NOT_BE_CONVERTED_MSG */
    private const VIDEO_CAN_NOT_BE_CONVERTED_MSG = 'File with title %s could not be converted';

    /** @var string MPD_FILENAME_REGEX */
    private const MPD_FILENAME_REGEX = '/(?<=\/)[a-z0-9]+\.mpd$/';

    /** @var string DASH_FILE_SOURCES_FOLDER */
    private const DASH_FILE_SOURCES_FOLDER = 'dash-file-sources';

    /** @var FileConverter $fileConverter */
    private FileConverter $fileConverter;

    /**
     * @param Logger $logger
     * @param string $playlistTitle
     * @param string|null $downloadsFolder
     * @param FileConverter $fileConverter
     */
    public function __construct(Logger $logger, string $playlistTitle, ?string $downloadsFolder, FileConverter $fileConverter)
    {
        parent::__construct($logger, $playlistTitle, $downloadsFolder);
        $this->fileConverter = $fileConverter;
    }

    /**
     * Save to HDD
     * @param array $videoData
     * @return bool
     * @throws Exception
     */
    public function saveToHDD(array $videoData): bool
    {
        // Source
        $source = $videoData['options']['src'];

        // Title
        $title = $videoData['options']['title'];

        // Parse the mpd to receive the individual audio & video
        $sources = MPDParser::parse($source);
        if (empty($sources['audio']) || empty($sources['video'])) {
            // Notify the user that the video is corrupted
            $this->log(self::CORRUPTED_DASH_VIDEO_DATA_MSG, [$title]);
            return false;
        }

        // Loop through audio/video files
        $convertedFiles = [];
        foreach ($sources as $mediaType => $sourceUrl) {
            // We need the mpd filename in order to generate temp folders
            $mpdFilename = $this->getMPDFilename($source);
            if (!$mpdFilename) {
                // Notify the user that the video is corrupted
                $this->log(self::CORRUPTED_DASH_VIDEO_DATA_MSG, [$title]);
                return false;
            }

            // Generate temp output folder
            $outputDir = $this->generateTempOutputDirName($mpdFilename);
            $this->createTempOutputDir($outputDir);

            // Generate temp output file name
            $filename = $this->generateTempOutputFileName($outputDir, $mediaType, $sourceUrl);

            // Generate file download url
            $downloadUrl = $this->generateFileDownloadUrl($mpdFilename, $sourceUrl, $source);

            // Save the temp file. We will need it for the conversion later
            if (false === $this->saveTempFile($filename, $downloadUrl)) {
                $this->log(self::CORRUPTED_MEDIA_MSG, [$mediaType, $title]);
                return false;
            }

            // We`ll delete them at a later time
            $convertedFiles[$mediaType] = $filename;
        }

        // Generate filename for the result of the conversion
        $outputFilename = $this->generateTempOutputFileName(
            pathinfo($convertedFiles['video'], PATHINFO_DIRNAME),
            self::OUTPUT_MEDIA_TYPE,
            $convertedFiles['video']
        );

        // Convert
        $converted = $this->fileConverter->convert(
            new DashVideoConvertableDto(
                $convertedFiles['video'],
                $convertedFiles['audio'],
                $outputFilename
            )
        );

        // Notify the user that the video has not been converted
        if (!$converted) {
            $this->log(self::VIDEO_CAN_NOT_BE_CONVERTED_MSG, [$title]);
            return false;
        }

        // Generate output filename to store it in downloads folder
        $downloadsOutputFilename = $this->generateDownloadsOutputFileName(
            $this->downloadSessionFolder,
            $title
        );

        // Move the converted files in the downloads folder
        $this->moveToDownloadFolder($outputFilename, $downloadsOutputFilename);

        // Remove temp files and temp directory
        $this->removeTempFiles($convertedFiles);
        $this->removeTempDirectory(pathinfo($convertedFiles['video'], PATHINFO_DIRNAME));

        // Notify the user that the video has been saved
        $this->log(self::FILE_SAVED_TO_MSG, [$title, $downloadsOutputFilename]);

        return true;
    }

    /**
     * Get MPD filename of the url. It is used for generating folders/files with this name
     * @param string $source
     * @return string
     */
    private function getMPDFilename(string $source): string
    {
        preg_match(self::MPD_FILENAME_REGEX, $source, $mpdFilename);
        return pos($mpdFilename);
    }

    /**
     * Generate dirname to store the individual audio/video dash-video files
     * @param string $mpdFilename
     * @return string
     */
    private function generateTempOutputDirName(string $mpdFilename): string
    {
        return sprintf(
            '%s/%s',
            self::DASH_FILE_SOURCES_FOLDER,
            pathinfo($mpdFilename, PATHINFO_FILENAME)
        );
    }

    /**
     * Create dir to store the individual audio/video dash files
     * @param string $outputDir
     * @return bool
     */
    private function createTempOutputDir(string $outputDir): bool
    {
        if (!is_dir($outputDir)) {
            return mkdir($outputDir);
        }

        return true;
    }

    /**
     * Generate the output filename of audio/video dash file
     * @param string $outputDir
     * @param string $mediaType
     * @param string $source
     * @return string
     */
    private function generateTempOutputFileName(string $outputDir, string $mediaType, string $source): string
    {
        return sprintf('%s/%s.%s', $outputDir, $mediaType, pathinfo($source, PATHINFO_EXTENSION));
    }

    /**
     * Generate file download url (based on mpd filename)
     * @param string $mpdFilename
     * @param string $sourceUrl
     * @param string $mpdFileSource
     * @return string
     */
    private function generateFileDownloadUrl(string $mpdFilename, string $sourceUrl, string $mpdFileSource): string
    {
        return str_replace($mpdFilename, $sourceUrl, $mpdFileSource);
    }

    /**
     * Save the temporary file
     * @param string $filename
     * @param string $downloadUrl
     * @return bool
     */
    private function saveTempFile(string $filename, string $downloadUrl): bool
    {
        return false !== Requester::make($downloadUrl, $filename);
    }

    /**
     * Returns list with the files which were not removed (empty if succeeded)
     * @param array $filenames
     * @return array
     */
    private function removeTempFiles(array $filenames): array
    {
        $fileNotRemoved = [];
        foreach ($filenames as $filename) {
            $fileRemoved = unlink($filename);
            if (!$fileRemoved) {
                $fileNotRemoved[] = $filename;
            }
        }

        return $fileNotRemoved;
    }

    /**
     * Removes temporary folder
     * @param string $dirname
     * @return bool
     */
    private function removeTempDirectory(string $dirname): bool
    {
        return rmdir($dirname);
    }

    /**
     * Move the converted file to downloads folder
     * @param string $sourceFilename
     * @param string $targetFilename
     * @return bool
     */
    private function moveToDownloadFolder(string $sourceFilename, string $targetFilename): bool
    {
        return rename($sourceFilename, $targetFilename);
    }
}
