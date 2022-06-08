<?php
require('common.php');

class AumQQHandler {
    public static $siteSearch = 'https://c.y.qq.com/soso/fcgi-bin/client_search_cp?aggr=1&format=json&cr=1&flag_qc=0&p=1&n=30&w=';
    public static $siteDownload = 'https://c.y.qq.com/lyric/fcgi-bin/fcg_query_lyric_new.fcg?format=json&g_tk=5381&';
    public static $siteHeader = array('Origin: https://y.qq.com', 'Referer: https://y.qq.com/n/ryqq/player');
    public static $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36';

    public static function getContent($url, $defaultValue) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,br');
        curl_setopt($curl, CURLOPT_USERAGENT, AumQQHandler::$userAgent);
        curl_setopt($curl, CURLOPT_HTTPHEADER, AumQQHandler::$siteHeader);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);

        if ($result === false) {
            return $defaultValue;
        } else {
            return AumQQHandler::getCleanJsonData($result);
        }
    }

    public static function search($word) {
        $results = array();
        $url = AumQQHandler::$siteSearch . urlencode($word);
        $jsonContent = AumQQHandler::getContent($url, '{"data":{"song":{"list":[]}}}');
        $json = json_decode($jsonContent, true);

        $songArray = $json['data']['song']['list'];
        foreach($songArray as $songItem) {
            $song = $songItem['songname'];
            $id = 'songmid=' . $songItem['songmid'] . '&musicid=' . $songItem['songid'];
            $singers = array();
            foreach ($songItem['singer'] as $singer) {
                array_push($singers, $singer['name']);
            }
            $des = $songItem['albumname'];

            array_push($results, array('song' => $song, 'id' => $id, 'singers' => $singers, 'des' => $des));
        }
        return $results;
    }

    public static function downloadLyric($songId) {
        $url = AumQQHandler::$siteDownload . $songId;
        $jsonContent = AumQQHandler::getContent($url, '{"lyric": "", "trans": ""}');
        $json = json_decode($jsonContent, true);
        $encodeLyric = $json['lyric'];
        $lyric = base64_decode($encodeLyric);
        // Chinese translation
        if (strlen($json['trans']) > 0) {
            $transLyric = base64_decode($json['trans']);
            $lyric = AumCommon::getChineseTranslationLrc($lyric, $transLyric);
        }
        return $lyric;
    }

    public static function getCleanJsonData($data) {
        if (preg_match('/^\w+\((\{.+})\)\s*$/', $data)) {
            preg_match('/^\w+\((\{.+})\)\s*$/', $data, $matches);
            return $matches[1];
        }
        return $data;
    }
}
