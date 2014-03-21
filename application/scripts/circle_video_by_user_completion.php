<?php

require_once dirname(__FILE__).'/bootstrap.php';

$modelCircleVideoByUser = new Model_Data_CircleVideoByUser();
$modelVideo = new Model_Data_Video();
$coll = $modelCircleVideoByUser->getCollection();
$cursor = $coll->find()->sort(array('_id' => 1));
foreach ($cursor as $doc) {
    if (!isset($doc['title']) || !isset($doc['tag'])) {
        $video = $modelVideo->get($doc['video_id']);
        if (!$video) {
            $video['title'] = '';
            $video['tag'] = array();
        }
        $modelCircleVideoByUser->update(array('_id' => $doc['_id']), array(
            'title' => $video['title'],
            'tag' => $video['tag']
        ));
    }
}
