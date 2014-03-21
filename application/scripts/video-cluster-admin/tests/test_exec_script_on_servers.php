#!/home/worker/php5/bin/php
<?php

require_once dirname(__FILE__).'/../common.php';

try {
    logMessage("begin ...");
    $script = "/home/worker/video-cluster-admin/tests/dummy_exec.php";
    execScriptOnRemoteServers(getClusterServers(), $script);
    logMessage("end");
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);

