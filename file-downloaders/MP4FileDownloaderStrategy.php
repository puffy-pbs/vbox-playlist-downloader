<?php

class MP4FileDownloaderStrategy extends FileDownloaderStrategy
{
    /** @var string VIDEO_IS_PRIVATE_OR_ERASED_MSG */
    private const VIDEO_IS_PRIVATE_OR_ERASED_MSG = 'Video is private or erased';

    /** @var string FILE_IS_CORRUPTED */
    private const FILE_IS_CORRUPTED_MSG = 'File with title %s has corrupted filename';

    /** @var string FILE_COULD_NOT_BE_SAVED */
    private const FILE_COULD_NOT_BE_SAVED_MSG = 'File with title %s could not be saved on HDD';

    /**
     * @param Logger $logger
     * @param string $playlistTitle
     * @param string|null $downloadsFolder
     */
    public function __construct(Logger $logger, string $playlistTitle, ?string $downloadsFolder)
    {
        parent::__construct($logger, $playlistTitle, $downloadsFolder);
    }

    /**
     * Save to HDD
     * @param array $videoData
     * @return bool
     */
    public function saveToHDD(array $videoData): bool
    {
        // If the video is private or erased
        if ($this->isBlankSrc($videoData)) {
            // Notify the user that the video is private/erased
            $this->log(self::VIDEO_IS_PRIVATE_OR_ERASED_MSG);
            return false;
        }

        // Source
        $source = $videoData['options']['src'];

        // Title
        $title = $videoData['options']['title'];

        // Extension
        $fileExtension = pathinfo($source, PATHINFO_EXTENSION);
        if (empty($fileExtension)) {
            // Notify the user that the video is corrupted
            $this->log(self::FILE_IS_CORRUPTED_MSG, [$title]);
            return false;
        }

        // Generate output filename to store it in downloads folder
        $downloadsOutputFilename = $this->generateDownloadsOutputFileName(
            $this->downloadSessionFolder,
            $title
        );

        // Download and save the file
        $isSaved = Requester::make($source, $downloadsOutputFilename);
        if (false === $isSaved) {
            // Notify the user that the video has not been saved
            $this->log(self::FILE_COULD_NOT_BE_SAVED_MSG, [$title]);
            return false;
        }

        // Notify the user that the video has been saved
        $this->log(self::FILE_SAVED_TO_MSG, [$title, $downloadsOutputFilename]);

        return true;
    }
}
