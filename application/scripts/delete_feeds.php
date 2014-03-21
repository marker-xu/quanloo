<?php

require_once dirname(__FILE__).'/bootstrap.php';

$shortopts = '';
$longopts = array(
    'type:',
    'begin:',
    'end:',
);
$options = getopt($shortopts, $longopts);
if (!isset($options['type']) || !isset($options['begin']) || !isset($options['end'])) {
    echo "Usage: ".basename(__FILE__)." --type=[video|circle|user] --begin='2012-03-30 17:00:00' --end='2012-03-31 02:00:00'\n";
    exit;
}
$begin = new DateTime($options['begin']);
$end = new DateTime($options['end']);

if ($options['type'] == 'video') {
    $model = new Model_Data_VideoFeed();
} else if ($options['type'] == 'circle') {
    $model = new Model_Data_CircleFeed();
} else if ($options['type'] == 'user') {
    $model = new Model_Data_UserFeed();
} else {
    Kohana::$log->error("unknown type: ".$options['type']);
    exit;
}

Kohana::$log->info("begin ...");
$result = $model->delete(array('create_time' => array(
	'$gte' => new MongoDate($begin->getTimestamp()),
	'$lt' => new MongoDate($end->getTimestamp())
)), array('safe' => true));
Kohana::$log->info("delete result", $result);
Kohana::$log->info("end");
