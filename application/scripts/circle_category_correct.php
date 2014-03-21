<?php

require_once dirname(__FILE__).'/bootstrap.php';

$modelDataCircle = new Model_Data_Circle();
$coll = $modelDataCircle->getCollection();
$cursor = $coll->find()->sort(array('_id' => 1));
foreach ($cursor as $circle) {
    if (!is_array($circle['category'])) {
        print_r($circle);
        $modelDataCircle->update(array('_id' => $circle['_id']), array(
            'category' => array($circle['category'])
        ));
    }
}
