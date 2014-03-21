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

$modelDataCircleStatAll = new Model_Data_CircleStatAll();
foreach ($result['retval'] as $doc) {
    $modelDataCircleStatAll->update(array(
    	'_id' => (int) $doc['circle_id']
    ), array(
        'user_count' => (int) $doc['user_count']
    ), array('upsert' => TRUE));
    Kohana::$log->info('update', $doc);
}
