<?php
require ('qqHandler.php');

class AumQQSource {
    private $mArtist = '';
    private $mTitle = '';
    public function __construct() {}

    public function getLyricsList($artist, $title, $info) {
        $artist = trim($artist);
        $this->mArtist = $artist;
        $title = trim($title);
        $this->mTitle = $title;
        $list = AumQQHandler::search($title);
        if (count($list) === 0) {
            return 0;
        }

        $exactMatchArray = array();
        $partialMatchArray = array();
        foreach ($list as $item) {
            $lowTitle = strtolower($title);
            $lowSong = strtolower($item['song']);

            if ($lowTitle === $lowSong) {
                array_push($exactMatchArray, $item);
            } elseif (strpos($lowSong, $lowTitle) !== false || strpos($lowTitle, $lowSong) !== false) {
                array_push($partialMatchArray, $item);
            }
        }

        $songArray = array();
        if (count($exactMatchArray) > 0) {
            $songArray = $exactMatchArray;
        } elseif (count($partialMatchArray) > 0) {
            $songArray = $partialMatchArray;
        }

        if (count($songArray) === 0) {
            return 0;
        }

        $foundArray = array();
        foreach ($songArray as $item) {
            $lowArtist = strtolower($artist);
            foreach ($item['singers'] as $singer) {
                $lowSinger = strtolower($singer);
                if (strpos($lowArtist, $lowSinger) !== false || strpos($lowSinger, $lowArtist) !== false) {
                    array_push($foundArray, $item);
                    break;
                }
            }
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
}

