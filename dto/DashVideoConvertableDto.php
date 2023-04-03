<?php

class DashVideoConvertableDto
{
    /** @var string $audioFilename */
    public string $audioFilename;

    /** @var string $videoFilename */
    public string $videoFilename;

    /** @var string $outputFilename */
    public string $outputFilename;

    public function __construct(string $audioFilename, string $videoFilename, string $outputFilename)
    {
        $this->audioFilename = $audioFilename;
        $this->videoFilename = $videoFilename;
        $this->outputFilename = $outputFilename;
    }
}
