<?php
require ('qqHandler.php');

class AumQQSource {
    private $mArtist = '';
    private $lowArtist = '';
    private $mTitle = '';
    private $lowTitle = '';
    public function __construct() {}

    public function getLyricsList($artist, $title, $info) {
        $artist = trim($artist);
        $this->mArtist = $artist;
        $this->lowArtist = strtolower($artist);

        $title = trim($title);
        $this->mTitle = $title;
        $this->lowTitle = strtolower($title);

        $list = AumQQHandler::search($title);
        if (count($list) === 0) {
            return 0;
        }

        $exactMatchArray = array();
        $partialMatchArray = array();
        foreach ($list as $item) {
            $lowSong = strtolower($item['song']);

            if ($this->lowTitle === $lowSong) {
                array_push($exactMatchArray, $item);
            } elseif ($this->isPartialMatch($lowSong, $this->lowTitle)) {
                array_push($partialMatchArray, $item);
            }
        }

        if (count($exactMatchArray) === 0 && count($partialMatchArray) === 0) {
            return 0;
        }

        $foundArray = array();
        if (count($exactMatchArray) > 0) {
            $foundArray = $this->findSongItems($exactMatchArray);
        }

        if (count($foundArray) === 0 && count($partialMatchArray) > 0) {
            $foundArray = $this->findSongItems($partialMatchArray);
        }

        if (count($foundArray) === 0) {
            return 0;
        }

        usort($foundArray, array($this, 'compare'));
        foreach ($foundArray as $item) {
            $info->addTrackInfoToList(implode('&', $item['singers']), $item['song'], $item['id'], $item['des']);
        }
        return count($foundArray);
    }

    public function getLyrics($id, $info) {
        $lyric = AumQQHandler::downloadLyric($id);
        if ($lyric === '') {
            return false;
        }

        $info->addLyrics($lyric, $id);
        return true;
    }

    private function compare($lhs, $rhs) {
        $scoreTitleL = $this->getStringSimilarPercent($this->mTitle, $lhs['song']);
        $scoreTitleR = $this->getStringSimilarPercent($this->mTitle, $rhs['song']);
        $scoreArtistL = $this->getStringSimilarPercent($this->mArtist, implode('&', $lhs['singers']));
        $scoreArtistR = $this->getStringSimilarPercent($this->mArtist, implode('&', $rhs['singers']));

        return $scoreTitleR + $scoreArtistR - $scoreTitleL - $scoreArtistL;
    }

    private function getStringSimilarPercent($lhs, $rhs) {
        similar_text($lhs, $rhs, $percent);
        return $percent;
    }

    private function isPartialMatch($lhs, $rhs) {
        return strpos($lhs, $rhs) !== false || strpos($rhs, $lhs) !== false;
    }

    private function findSongItems($songArray) {
        $foundArray = array();
        foreach ($songArray as $item) {
            foreach ($item['singers'] as $singer) {
                $lowSinger = strtolower($singer);
                if ($this->isPartialMatch($this->lowArtist, $lowSinger)) {
                    array_push($foundArray, $item);
                    break;
                }
            }
        }
        return $foundArray;
    }
}

