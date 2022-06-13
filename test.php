<?php
require('debug.php');
require('src/qqSource.php');

$downloader = (new ReflectionClass('AumQQSource'))->newInstance();
$testArray = array(
    array('title' => '별', 'artist' => 'Loco&俞胜恩'),
    array('title' => 'Reign Fall', 'artist' => 'Chamillionaire&Scarface&Killer Mike')
);

foreach ($testArray as $key => $item) {
    echo "\n++++++++++++++++++++++++++++++\n";
    echo "测试 $key 开始...\n";
    if ($key > 0) {
        echo "等待 5 秒...\n";
        sleep(5);
    }
    $testObj = new AudioStationResult();
    $count = $downloader->getLyricsList($item['artist'], $item['title'], $testObj);
    if ($count > 0) {
        $item = $testObj->getFirstItem();
        $downloader->getLyrics($item['id'], $testObj);
    } else {
        echo "没有查找到任何歌词！\n";
    }
    echo "测试 $key 结束。\n";
}
