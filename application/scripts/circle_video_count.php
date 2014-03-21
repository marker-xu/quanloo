<?php

require_once dirname(__FILE__).'/bootstrap.php';

$keys = array('circle_id' => 1);
$initial = array('video_count' => 0);
$reduce = "function (doc, prev) { 
	prev.video_count += 1;
}";
$modelDataCircleVideoByUser = new Model_Data_CircleVideoByUser();
MongoCursor::$timeout = -1;
$result = $modelDataCircleVideoByUser->group($keys, $initial, $reduce);
if (!$result['ok']) {
    Kohana::$log->info('group failed', $result);
    exit();
}

$modelDataCircleStatAll = new Model_Data_CircleStatAll();
$modelDataCircle = new Model_Data_Circle();
foreach ($result['retval'] as $doc) {
    if (!$modelDataCircle->findOne(array('_id' => (int) $doc['circle_id']))) {
        continue;
    }
    $modelDataCircleStatAll->update(array(
    	'_id' => (int) $doc['circle_id']
    ), array(
        'video_count' => (int) $doc['video_count']
    ), array('upsert' => TRUE));
    Kohana::$log->info('update', $doc);
}
