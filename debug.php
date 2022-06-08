<?php
require('src/qqSource.php');

class AudioStationResult {
    private $items;
    public function __construct() {
        $this->items = array();
    }

    public function addTrackInfoToList($artist, $title, $id, $partialLyric) {
        printf("\nartist = %s\n", $artist);
        printf("title = %s\n", $title);
        printf("id = %s\n", $id);
        printf("partialLyric = %s\n\n", $partialLyric);

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
        printf("***** END OF LYRIC *****\n\n");
    }

    public function getFirstItem() {
        if (count($this->items) > 0) {
            return $this->items[0];
        }
        return null;
    }
}

$title = 'DDU-DU DDU-DU (Korean Ver.)';
$artist = 'BLACKPINK';

echo "测试开始...\n变量:<title = $title; artist = $artist>\n";
$testObj = new AudioStationResult();
$downloader = (new ReflectionClass('AumQQSource'))->newInstance();
$count = $downloader->getLyricsList($artist, $title, $testObj);
if ($count > 0) {
    $item = $testObj->getFirstItem();
    $downloader->getLyrics($item['id'], $testObj);
} else {
    echo "\n没有查找到任何歌词！\n";
}
