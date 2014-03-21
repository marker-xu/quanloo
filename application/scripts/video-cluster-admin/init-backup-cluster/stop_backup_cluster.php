#!/home/worker/php5/bin/php
<?php

/**
 * 停止后备集群
 * @author wangjiajun
 */

require_once dirname(__FILE__).'/../common.php';

try {
    $cluster = getBackupCluster();
    logMessage("backup cluster is $cluster");
    
    logMessage("stop backup cluster begin ...");
    $stopScriptPath = getClusterStopScriptPath($cluster);
    $dir = dirname($stopScriptPath);
    $file = basename($stopScriptPath);
    $cmd = "cd $dir; ./$file";
    logMessage($cmd);
    $output = shell_exec($cmd);
    logMessage("stop backup cluster end");
    
    logMessage("wait backup cluster port closed begin ...");
    $ports = getClusterPortList($cluster);
    waitLocalPortClose($ports, 100);
    logMessage("wait backup cluster port closed end");
    
    logMessage("stop backup mongos");
    stopMongos(getBackupMongos());
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);
