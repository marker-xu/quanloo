<?php

require_once dirname(__FILE__).'/bootstrap.php';

try {
    $modelDataCircleCandidate = new Model_Data_CircleCandidate();
    $coll = $modelDataCircleCandidate->getCollection();
    $cursor = $coll->find()->sort(array('create_time' => -1));
    $modelLogicSearch = new Model_Logic_Search();
    $count = 0;
    foreach ($cursor as $circle) {
        $doc = array();
        if (!isset($circle['video_count'])) {
            $doc['video_count'] = 0;
            foreach ($circle['tag'] as $tag) {
                $result = $modelLogicSearch->search($tag);
                $doc['video_count'] += isset($result['real_total']) ? (int) $result['real_total'] : 0;
            }
        }
        if (!isset($circle['submitted_count'])) {
             $doc['submitted_count'] = 1;
        }
        if ($doc) {
            $modelDataCircleCandidate->update(array('_id' => $circle['_id']), $doc);
            $count++;
        }
    }
    Kohana::$log->info("count $count");
} catch (Exception $e) {
    Kohana::$log->error($e);
    exit;
}
