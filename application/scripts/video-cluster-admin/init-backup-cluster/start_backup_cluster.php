#!/home/worker/php5/bin/php
<?php

/**
 * 启动后备集群
 * @author wangjiajun
 */

require_once dirname(__FILE__).'/../common.php';

try {
    $cluster = getBackupCluster();
    logMessage("backup cluster is $cluster");
    
    logMessage("start backup cluster begin ...");
    $startScriptPath = getClusterStartScriptPath($cluster);
    $dir = dirname($startScriptPath);
    $file = basename($startScriptPath);
    $cmd = "cd $dir; ./$file";
    logMessage($cmd);
    $output = shell_exec($cmd);
    logMessage("start backup cluster end");
    
    logMessage("wait backup cluster port open begin ...");
    $ports = getClusterPortList($cluster);
    waitLocalPortOpen($ports, 100);
    logMessage("wait backup cluster port open end");
    
    logMessage("start backup mongos");
    startMongos(getBackupMongos());
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);
