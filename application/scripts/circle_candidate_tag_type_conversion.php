<?php

require_once dirname(__FILE__).'/bootstrap.php';

try {
    $modelDataCircleCandidate = new Model_Data_CircleCandidate();
    $coll = $modelDataCircleCandidate->getCollection();
    $cursor = $coll->find()->sort(array('create_time' => -1));
    $count = 0;
    foreach ($cursor as $circle) {
        $modelDataCircleCandidate->update(array('_id' => $circle['_id']), array(
            'tag' => array_values($circle['tag'])
        ));
    }
    $count++;
    Kohana::$log->info("count $count");
} catch (Exception $e) {
    Kohana::$log->error($e);
    exit;
}
