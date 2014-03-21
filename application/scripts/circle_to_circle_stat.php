<?php

require_once dirname(__FILE__).'/bootstrap.php';

Kohana::$log->info("begin ...");
try {
    $modelDataCircle = new Model_Data_Circle();
    $modelDataCircleStatRecent = new Model_Data_CircleStatRecent();
    $modelDataCircleStatAll = new Model_Data_CircleStatAll();
    $coll = $modelDataCircle->getCollection();
    $cursor = $coll->find()->sort(array('_id' => 1));
    $count = 0;
    foreach ($cursor as $circle) {
        $modelDataCircleStatRecent->update(array('_id' => $circle['_id']), array(
            'category' => $circle['category'],
            'status' => $circle['status']
        ));
        $modelDataCircleStatAll->update(array('_id' => $circle['_id']), array(
            'category' => $circle['category'],
            'status' => $circle['status']
        ), array('upsert' => true));
        $count++;
    }
} catch (Exception $e) {
    Kohana::$log->error($e->getMessage());
    exit;
}
Kohana::$log->info("end, count-$count");
