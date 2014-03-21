<?php

require_once dirname(__FILE__).'/bootstrap.php';

try {
    $modelDataCircle = new Model_Data_Circle();
    $coll = $modelDataCircle->getCollection();
    $cursor = $coll->find()->sort(array('_id' => -1));
    $count = 0;
    foreach ($cursor as $circle) {
        if (!isset($circle['filter_tag']) || !$circle['filter_tag'] 
            || isset($circle['filter_tag']['name'])) {
            $modelDataCircle->update(array('_id' => $circle['_id']), array(
                'filter_tag' => array(
                    array('name' => '热点', 'tag' => array(), 'default' => true)
                )
            ));
        }
    }
    $count++;
    Kohana::$log->info("count $count");
} catch (Exception $e) {
    Kohana::$log->error($e);
    exit;
}
