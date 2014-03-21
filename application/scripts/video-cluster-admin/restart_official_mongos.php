#!/home/worker/php5/bin/php
<?php

require_once dirname(__FILE__).'/common.php';

try {
    logMessage("restart mongos begin ...");
    $script = "/home/worker/video-cluster-admin/mongos/restart_official_mongos.php";
    execScriptOnRemoteServers(getMongosServers(), $script);
    logMessage("restart mongos end");
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);