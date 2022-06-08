<?php

class AumCommon {
    public static function isValidLrcTime($str) {
        if (trim($str) === '' || strlen($str) !== 10 || $str[0] !== '[' || $str[9] !== ']') {
            return false;
        }

        for ($count = 1; $count < 9; $count++) {
            $ch = $str[$count];
            if ($ch !== ':' && $ch !== '.' && !is_numeric($ch)) {
                return false;
            }
        }

        return true;
    }

    public static function isValidLrcText($str) {
        if (trim($str) === '' || trim($str) === '//') {
            return false;
        }
        return true;
    }

    public static function getTimeFromTag($tag) {
        $min = substr($tag, 1, 2);
        $sec = substr($tag, 4, 2);
        $mil = substr($tag, 7, 2);
        return $mil + $sec * 100 + $min * 60 * 100;
    }

    public static function processLrcLine($lrc) {
        $result = array();
        foreach (explode("\n", $lrc) as $line) {
            $key = substr($line, 0, 10);
            $value = substr($line, 10, strlen($line) -10);
            if (!AumCommon::isValidLrcTime($key) || !AumCommon::isValidLrcText($value)) {
                $key = '';
                $value = $line;
            }
            array_push($result, array('tag' => $key, 'lrc' => $value));
        }
        return $result;
    }

    public static function getChineseTranslationLrc($orgLrc, $transLrc) {
        $resultLrc = '';
        $orgLines = AumCommon::processLrcLine($orgLrc);
        $transLines = AumCommon::processLrcLine($transLrc);

        $transCursor = 0;
        foreach ($orgLines as $line) {
            $key = $line['tag'];
            $value = $line['lrc'];
            $resultLrc .= $key . $value;

            $trans = '';
            if ($key !== '') {
                $time = AumCommon::getTimeFromTag($key);
                for ($i = $transCursor; $i < count($transLines); $i++) {
                    $tKey = $transLines[$i]['tag'];
                    if (AumCommon::getTimeFromTag($tKey) > $time) {
                        $transCursor = $i;
                        break;
                    }

                    $tValue = $transLines[$i]['lrc'];
                    if ($key === $tKey) {
                        $transCursor = $i + 1;
                        $trans = $tValue;
                        break;
                    }
                }
            }

            if ($trans !== '') {
                $resultLrc .= ' 【' . $trans . '】';
            }
            $resultLrc .= "\n";
        }
        return $resultLrc;
    }
}