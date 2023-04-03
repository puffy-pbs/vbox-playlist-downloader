<?php

interface FileDownloader
{
    public function saveToHDD(array $videoData): bool;

    public function getFolderForThisDownloadSession(): string;
}
