<?php

require_once dirname(__FILE__).'/bootstrap.php';

$dt = new DateTime('30 days ago');
$dt->setTime(0, 0);

Kohana::$log->info("begin ...");
try {
    $modelDataVideoFeed = new Model_Data_VideoFeed();
    $modelDataVideoFeed->delete(array('create_time' => array(
    	'$lt' => new MongoDate($dt->getTimestamp())
    )));
    
    $modelDataCircleFeed = new Model_Data_CircleFeed();
    $modelDataCircleFeed->delete(array('create_time' => array(
    	'$lt' => new MongoDate($dt->getTimestamp())
    )));
    
    $modelDataUserFeed = new Model_Data_UserFeed();
    $modelDataUserFeed->delete(array('create_time' => array(
    	'$lt' => new MongoDate($dt->getTimestamp())
    )));
} catch (Exception $e) {
    Kohana::$log->error($e->getMessage());
    exit;
}
Kohana::$log->info("end");

