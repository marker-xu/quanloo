<?php

require_once dirname(__FILE__).'/bootstrap.php';

$keys = array('creator' => 1);
$initial = array('created_circle_count' => 0, 'created_public_circle_count' => 0);
$reduce = "function (doc, prev) { 
	if (doc.status == 1 || doc.status == 2) {
		prev.created_circle_count += 1;
	}
	if (doc.status == 1) {
		prev.created_public_circle_count += 1;
	}
}";
$modelDataCircle = new Model_Data_Circle();
MongoCursor::$timeout = -1;
$result = $modelDataCircle->group($keys, $initial, $reduce);
if (!$result['ok']) {
    Kohana::$log->info('group failed', $result);
    exit();
}

$modelDataUserStatAll = new Model_Data_UserStatAll();
foreach ($result['retval'] as $doc) {
    $modelDataUserStatAll->update(array(
    	'_id' => (int) $doc['creator']
    ), array(
        'created_circle_count' => (int) $doc['created_circle_count'],
        'created_public_circle_count' => (int) $doc['created_public_circle_count']
    ));
    Kohana::$log->info('update', $doc);
}
