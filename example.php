<?php

require_once('autoload.php');

try {
    // Examples
    $playlistUrls = [
         // Playlist with videos using MPD file format
        'https://www.vbox7.com/play:a73cf2f3cb?p=playlist&id=2259661',
         // - Playlist with mixed files (videos using MPD files to represent their origins
        // and ordinary videos with just one url to fetch their contents)
        'https://www.vbox7.com/play:aeab7e26?p=playlist&id=579436',
    ];

    // Download the videos from both playlists
    foreach ($playlistUrls as $playlistUrl) {
        VboxPlaylistProcessor::download($playlistUrl);
    }

} catch (Exception $e) {
    var_dump($e->getMessage());
}

