<?php

class MPDParser
{
    /**
     * @param string $url
     * @return array
     * @throws Exception
     */
    public static function parse(string $url): array
    {
        // Download mpd contents
        $mpdFile = file_get_contents($url);
        if (false === $mpdFile) {
            throw new RuntimeException('MPD file could not be parsed');
        }

        // Load the mpd into the xml parser
        $mpd = new SimpleXMLElement($mpdFile);

        // The audio/video urls of the dash video
        $urls = self::getBaseUrls($mpd);

        // We need them sorted because the video with the highest quality definition (e.g. 480p) matches everytime the audio
        sort($urls);

        $len = count($urls);

        return [
            'audio' => $urls[$len - 2],
            'video' => $urls[$len - 1],
        ];
    }

    /**
     * We retrieve the urls. The mpd file has separate audio and video files we need to extract
     * @param SimpleXMLElement $mpd
     * @return array
     */
    private static function getBaseUrls(SimpleXMLElement $mpd): array
    {
        $urls = [];
        foreach ($mpd->Period[0]->AdaptationSet as $adaptationSet) {
            foreach ($adaptationSet->Representation as $representation) {
                $urls[] = strval($representation->BaseURL);
            }
        }

        return $urls;
    }
}
