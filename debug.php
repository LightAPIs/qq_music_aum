<?php

class AudioStationResult {
    private $items;
    public function __construct() {
        $this->items = array();
    }

    public function addTrackInfoToList($artist, $title, $id, $partialLyric) {
        printf("\nartist = %s\n", $artist);
        printf("title = %s\n", $title);
        printf("id = %s\n", $id);
        printf("partialLyric = %s\n", $partialLyric);

        array_push($this->items, array(
            'artist' => $artist,
            'title' => $title,
            'id' => $id,
            'partialLyric' => $partialLyric,
        ));
    }

    public function addLyrics($lyric, $id) {
        printf("\nsong id: %s\n", $id);
        printf("song lyric:\n");
        printf("***** BEGIN OF LYRIC *****\n");
        printf("%s\n", $lyric);
        printf("***** END OF LYRIC *****\n");
    }

    public function getFirstItem() {
        if (count($this->items) > 0) {
            return $this->items[0];
        }
        return null;
    }
}
