#!/home/worker/php5/bin/php
<?php

/**
 * 重启Mongos
 * @author wangjiajun
 */

require_once dirname(__FILE__).'/../common.php';

try {    
    logMessage("restart mongos begin ...");
    stopMongos(getBackupMongos());
    stopMongos(getOfficialMongos());
    startMongos(getBackupMongos());
    startMongos(getOfficialMongos());
    logMessage("restart mongos end");
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);
