<?php
require ('qqHandler.php');

class AumQQSource {
    private $mArtist = '';
    private $cArtist = '';
    private $mTitle = '';
    private $cTitle = '';
    public function __construct() {}

    public function getLyricsList($artist, $title, $info) {
        $artist = trim($artist);
        $this->mArtist = $artist;
        $this->cArtist = $this->getCleanStr($artist);

        $title = trim($title);
        $this->mTitle = $title;
        $this->cTitle = $this->getCleanStr($title);

        $list = AumQQHandler::search($this->mTitle, $this->mArtist);
        if (count($list) === 0) {
            return 0;
        }

        $exactMatchArray = array();
        $partialMatchArray = array();
        foreach ($list as $item) {
            $cSong = $this->getCleanStr($item['song']);
            if ($this->cTitle === $cSong) {
                array_push($exactMatchArray, $item);
            } elseif ($this->isPartialMatch($cSong, $this->cTitle)) {
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

        $info->addLyrics($this->decodeHtmlSpecialChars($lyric), $id);
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
                $cSinger = $this->getCleanStr($singer);
                if ($this->isPartialMatch($this->cArtist, $cSinger)) {
                    array_push($foundArray, $item);
                    break;
                }
            }
        }
        return $foundArray;
    }

    private function decodeHtmlSpecialChars($str) {
        return htmlspecialchars_decode($str, ENT_QUOTES | ENT_HTML5);
    }

    private function getCleanStr($str) {
        $lowStr = strtolower($str);
        return str_replace(
            array(" ", "，", "：", "；", "！", "？", "「", "」", "（", "）", "。"),
            array("", ",", ":", ";", "!", "?", "｢", "｣", "(", ")", "."),
            $lowStr);
    }
}

