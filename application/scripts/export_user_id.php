<?php

require_once dirname(__FILE__).'/bootstrap.php';

$modelDataUser = new Model_Data_User();
$coll = $modelDataUser->getCollection();
$cursor = $coll->find(array(), array('_id'));
foreach ($cursor as $doc) {
    echo $doc['_id']."\n";
}
