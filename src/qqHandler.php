<?php
require('qqTranslation.php');

class AumQQHandler {
    public static $siteSearch = 'https://u.y.qq.com/cgi-bin/musicu.fcg';
    public static $siteDownload = 'https://c.y.qq.com/lyric/fcgi-bin/fcg_query_lyric_new.fcg?format=json&g_tk=5381&';
    public static $siteSHeader = array('Host: u.y.qq.com');
    public static $siteLHeader = array('Origin: https://y.qq.com', 'Referer: https://y.qq.com/n/ryqq/player');
    public static $userSAgent = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)';
    public static $userLAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36';

    public static function getContent($url, $defaultValue, $siteHeader, $userAgent, $isPost = false, $postParams = null) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate,br');
        curl_setopt($curl, CURLOPT_POST, $isPost);
        if ($isPost) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postParams);
        }
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $siteHeader);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);

        if ($result === false) {
            return $defaultValue;
        } else {
            return $result;
        }
    }

    public static function search($title, $artist) {
        $results = array();
        $url = self::$siteSearch . "?pcachetime=" . self::getNowTimeStamp(10);
        $query = $artist . " - " . $title;
        $params = <<<EOD
{
   "comm" : {
      "_channelid" : "0",
      "_os_version" : "6.1.7601-2%2C+Service+Pack+1",
      "authst" : "",
      "ct" : "19",
      "cv" : "1873",
      "guid" : "",
      "patch" : "118",
      "psrf_access_token_expiresAt" : 0,
      "psrf_qqaccess_token" : "",
      "psrf_qqopenid" : "",
      "psrf_qqunionid" : "",
      "tmeAppID" : "qqmusic",
      "tmeLoginType" : 2,
      "uin" : "0",
      "wid" : "0"
   },
   "music.search.SearchCgiService" : {
      "method" : "DoSearchForQQMusicDesktop",
      "module" : "music.search.SearchCgiService",
      "param" : {
         "grp" : 1,
         "num_per_page" : 40,
         "page_num" : 1,
         "query" : "$query",
         "remoteplace" : "txt.newclient.top",
         "search_type" : 0,
         "searchid" : ""
      }
   }
}
EOD;

        $jsonContent = self::getContent($url, '{"music.search.SearchCgiService":{"data":{"body":{"song":{"list":[]}}}}}', self::$siteSHeader, self::$userSAgent, true, $params);
        $json = json_decode($jsonContent, true);

        $songArray = $json['music.search.SearchCgiService']['data']['body']['song']['list'];
        foreach($songArray as $songItem) {
            $song = $songItem['name'];
            $id = 'songmid=' . $songItem['mid'] . '&musicid=' . $songItem['id'];
            $singers = array();
            foreach ($songItem['singer'] as $singer) {
                array_push($singers, $singer['name']);
            }
            $des = $songItem['album']['name'];
            if ($des === '' || $des === null) {
                $des = $songItem['title'];
            }

            array_push($results, array('song' => $song, 'id' => $id, 'singers' => $singers, 'des' => $des));
        }
        return $results;
    }

    public static function downloadLyric($songId) {
        $url = self::$siteDownload . $songId;
        $jsonContent = self::getContent($url, '{"lyric": "", "trans": ""}', self::$siteLHeader, self::$userLAgent);
        $json = json_decode($jsonContent, true);
        $encodeLyric = $json['lyric'];
        $lyric = base64_decode($encodeLyric);
        // Chinese translation
        if (strlen($json['trans']) > 0) {
            $transLyric = base64_decode($json['trans']);
            $tl = new AumQQTranslation($lyric, $transLyric);
            $lyric = $tl->getChineseTranslationLrc();
        }
        return $lyric;
    }

    public static function getNowTimeStamp($len) {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return substr($msectime, 0, $len);
    }
}
