<?php

require_once dirname(__FILE__).'/bootstrap.php';

$keys = array('circle_id' => 1);
$initial = array('user_count' => 0);
$reduce = "function (doc, prev) { 
	prev.user_count += 1;
}";
$modelDataCircleUser = new Model_Data_CircleUser();
MongoCursor::$timeout = -1;
$result = $modelDataCircleUser->group($keys, $initial, $reduce);
if (!$result['ok']) {
    Kohana::$log->info('group failed', $result);
    exit();
}

usort($result['retval'], function ($a, $b) {
    if ($a['user_count'] < $b['user_count']) {
        return 1;
    } else if ($a['user_count'] > $b['user_count']) {
        return -1;
    } else {
        return 0;
    }
});

$circles = array_slice($result['retval'], 0, 500);
$modelDataCircle = new Model_Data_Circle();
foreach ($circles as $circle) {
    $doc = $modelDataCircle->findOne(array('_id' => $circle['circle_id']));
    if (!$doc) {
        continue;
    }
    $tagsCount = 0;
    if (isset($doc['filter_tag'])) {
        foreach ($doc['filter_tag'] as $value) {
            $tagsCount += count($value['tag']);            
        }
    }
    if ($tagsCount == 0) {
        echo $doc['_id']."\t".$doc['title']."\t".$circle['user_count']."\n";
    }
}
