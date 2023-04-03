<?php

final class VboxPlaylistProcessor implements VideoProcessor
{
    /** @var string SITE_DOMAIN */
    private const SITE_DOMAIN = 'https://www.vbox7.com';

    /** @var string PLAYLIST_VIDEO_URL_TEMPLATE */
    private const PLAYLIST_VIDEO_URL_TEMPLATE = '%s/aj/playlist/videos?p=playlist&id=%s&mdkey=%s&shuffle=';

    /** @var string VIDEO_URL_TEMPLATE */
    private const VIDEO_URL_TEMPLATE = '%s/aj/player/video/options?vid=%s&isEventPoster=0;';

    /** @var string PLAYLIST_ID_REGEX */
    private const PLAYLIST_ID_REGEX = '/(?<=p=playlist&id=)[a-z0-9]+$/';

    /** @var string VIDEO_ID_REGEX */
    private const VIDEO_ID_REGEX = '/(?<=\/play:)[a-z0-9]+(?=\?p)/';

    /** @var string PLAYLIST_TITLE_XPATH */
    private const PLAYLIST_TITLE_XPATH = "//h1[@data-id='playlist-cont']";

    /** @var string PLAYLIST_LIST_XPATH */
    private const PLAYLIST_LIST_XPATH = "//section[@class='playlist-list']//a";

    /**
     * The main functionality starts here
     * @param $source
     * @return void
     */
    public static function download($source): void
    {
        if (!is_string($source)) {
            throw new InvalidArgumentException('The url should be a string');
        }

        if (!self::isPlaylistUrl($source)) {
            throw new InvalidArgumentException('The url should be a playlist url');
        }

        // We need to generate the scrape playlist url
        $playlistId = self::getPlaylistId($source);
        $videoId = self::getVideoId($source);
        $scrapePlaylistUrl = self::generateScrapePlaylistUrl($playlistId, $videoId);

        // Start the process
        self::processPlaylist($scrapePlaylistUrl);
    }

    /**
     * Process playlist
     * @param string $playlistUrl
     * @return void
     */
    private static function processPlaylist(string $playlistUrl): void
    {
        // Get the html of the page
        $html = Requester::make($playlistUrl);
        if (false === $html) {
            throw new RuntimeException('Source of the page could not be downloaded. Please try again.');
        }

        // Load the scraped html
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        if (!@$domDocument->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>')) {
            throw new RuntimeException('Html could not be parsed. Please try again.');
        }

        // Get the videos from the playlist
        $domXpath = new DOMXPath($domDocument);
        $videos = $domXpath->query(self::PLAYLIST_LIST_XPATH);
        $playlistTitle = self::getPlayListTitle(
            $domXpath->query(self::PLAYLIST_TITLE_XPATH)
        );

        // Loop through videos
        if (!empty($videos)) {
            // The downloader would be set on the 1st successful run
            $downloadsFolder = null;

            foreach ($videos as $video) {
                // Wait for some time to not overload the vbox site
                Delay::wait();

                // Get scraped data
                $videoData = self::getVideoData($video);

                // Validate data
                VideoDataValidator::validate($videoData);

                // Get the downloader.
                // We do this because one playlist can have different videos - one using mpd
                // and the other using just an url to fetch the source
                $downloader = FileDownloaderResolver::getDownloader(
                    $videoData['options']['src'], $playlistTitle, $downloadsFolder
                );
                $downloadsFolder = $downloadsFolder ?: $downloader->getFolderForThisDownloadSession();

                // Save to hdd
                $downloader->saveToHDD($videoData);
            }
        }
    }

    /**
     * Get video id
     * @param string $source
     * @return string
     */
    private static function getVideoId(string $source): string
    {
        preg_match(self::VIDEO_ID_REGEX, $source, $videoId);
        return pos($videoId);
    }

    /**
     * Generate playlist url ready to be scraped ;)
     * @param string $playlistId
     * @param string $videoId
     * @return string
     */
    private static function generateScrapePlaylistUrl(string $playlistId, string $videoId): string
    {
        return sprintf(self::PLAYLIST_VIDEO_URL_TEMPLATE,self::SITE_DOMAIN, $playlistId, $videoId);
    }

    /**
     * Is the given playlist valid?
     * @param string $source
     * @return bool
     */
    private static function isPlaylistUrl(string $source): bool
    {
        return preg_match(self::PLAYLIST_ID_REGEX, $source);
    }

    /**
     * Get playlist id
     * @param string $source
     * @return string
     */
    private static function getPlaylistId(string $source): string
    {
        preg_match(self::PLAYLIST_ID_REGEX, $source, $playlistId);
        return pos($playlistId);
    }

    /**
     * Generate video url ready to be scraped ;)
     * @param string $videoId
     * @return string
     */
    private static function getScrapeVideoUrl(string $videoId): string
    {
        return sprintf(self::VIDEO_URL_TEMPLATE, self::SITE_DOMAIN, $videoId);
    }

    private static function getPlayListTitle(DOMNodeList $playlist): string
    {
        return $playlist->item(0)->textContent ?? '';
    }

    /**
     * Retrieve the scraped video data
     * @param DOMElement $video
     * @return array
     */
    private static function getVideoData(DOMElement $video): array
    {
        $videoHref = $video->getAttribute('href');
        $videoId = self::getVideoId($videoHref);
        $scrapeVideoUrl = self::getScrapeVideoUrl($videoId);
        $videoData = Requester::make($scrapeVideoUrl) ?? [];
        return json_decode($videoData, true);
    }
}
