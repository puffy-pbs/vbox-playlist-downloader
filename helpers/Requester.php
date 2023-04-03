<?php

final class Requester
{
    /** @var int DEFAULT_TIMEOUT */
    private const DEFAULT_TIMEOUT = 120;

    /** @var int DEFAULT_MAX_REDIRECTS */
    private const DEFAULT_MAX_REDIRECTS = 20;

    /** @var string USER_AGENT */
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:97.0) Gecko/20100101 Firefox/97.0 Herring/93.1.6100.1';

    /**
     * A simple curl request
     * @param string $source
     * @param string|null $filename
     * @return bool|string
     */
    public static function make(string $source, string $filename = null)
    {
        $curlHandler = curl_init();
        $options = [
            CURLOPT_URL => $source,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_MAXREDIRS => self::DEFAULT_MAX_REDIRECTS,
            CURLOPT_USERAGENT => self::USER_AGENT,
        ];

        if ($filename) {
            $fp = fopen($filename, 'w+b');
            $options[CURLOPT_FILE] = $fp;
        }

        $curlOptionsSet = curl_setopt_array($curlHandler, $options);

        if (false === $curlOptionsSet) {
            return false;
        }

        $response = curl_exec($curlHandler);
        if (false === $response) {
            return false;
        }

        curl_close($curlHandler);

        return $response;
    }
}
