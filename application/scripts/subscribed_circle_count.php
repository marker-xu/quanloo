<?php

require_once dirname(__FILE__).'/bootstrap.php';

$keys = array('user_id' => 1);
$initial = array('subscribed_circle_count' => 0);
$reduce = "function (doc, prev) { 
	prev.subscribed_circle_count += 1;
}";
$modelDataCircleUser = new Model_Data_CircleUser();
MongoCursor::$timeout = -1;
$result = $modelDataCircleUser->group($keys, $initial, $reduce);
if (!$result['ok']) {
    Kohana::$log->info('group failed', $result);
    exit();
}

$modelDataUserStatAll = new Model_Data_UserStatAll();
foreach ($result['retval'] as $doc) {
    $modelDataUserStatAll->update(array(
    	'_id' => (int) $doc['user_id']
    ), array(
        'subscribed_circle_count' => (int) $doc['subscribed_circle_count']
    ), array('upsert' => TRUE));
    Kohana::$log->info('update', $doc);
}
