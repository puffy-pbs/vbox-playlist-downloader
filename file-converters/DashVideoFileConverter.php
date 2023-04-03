<?php

class DashVideoFileConverter implements FileConverter
{
    /**
     * Prepare command to combine audio & video .mp4 file
     * @param DashVideoConvertableDto $dto
     * @return string
     */
    private function prepareCommand(DashVideoConvertableDto $dto): string
    {
        $command = ['ffmpeg', '-hide_banner', '-loglevel', 'error', '-i', $dto->videoFilename, '-i',
            $dto->audioFilename, '-c:v', 'copy', $dto->outputFilename, '-y'];
        return implode(' ', $command);
    }

    /**
     * Execute converted command
     * @param DashVideoConvertableDto $dto
     * @return bool
     */
    public function convert(DashVideoConvertableDto $dto): bool
    {
        return false !== exec($this->prepareCommand($dto));
    }
}
