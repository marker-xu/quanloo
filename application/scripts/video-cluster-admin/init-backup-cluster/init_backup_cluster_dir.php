#!/home/worker/php5/bin/php
<?php

/**
 * 初始化后备集群目录
 * @author wangjiajun
 */

require_once dirname(__FILE__).'/../common.php';

try {
    $cluster = getBackupCluster();
    logMessage("backup cluster is $cluster");
    
    logMessage("remove backup cluster dir begin ...");
    $clusterDir = getClusterDir($cluster);
    $cmd = "rm -rf $clusterDir";
    logMessage($cmd);
    shell_exec($cmd);
    logMessage("remove backup cluster dir end");
    
    checkDiskSize();
    
    logMessage("unpack cluster package begin ...");
    $packagePath = getClusterPackagePath($cluster);
    $cmd = "tar zxf $packagePath -C ".dirname($clusterDir);
    logMessage($cmd);
    shell_exec($cmd);
    logMessage("unpack cluster package end");
    
    logMessage("set cluster as backup");
    $info = array('role' => 'backup');
    saveClusterInfo($cluster, $info);
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);
