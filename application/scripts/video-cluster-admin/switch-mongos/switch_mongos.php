#!/home/worker/php5/bin/php
<?php

/**
 * 交换Mongos配置
 * @author wangjiajun
 */

require_once dirname(__FILE__).'/../common.php';

try {    
    logMessage("switch config port");
    $config1 = file_get_contents(MONGOS1_CONFIG_PATH);
    $config2 = file_get_contents(MONGOS2_CONFIG_PATH);
    $pattern = '/port = (\d+)/m';
    preg_match($pattern, $config1, $matches);
    $port1 = $matches[1];
    preg_match($pattern, $config2, $matches);
    $port2= $matches[1];
    $config1 = preg_replace($pattern, "port = $port2", $config1);
    $config2 = preg_replace($pattern, "port = $port1", $config2);
    file_put_contents(MONGOS1_CONFIG_PATH, $config1);
    file_put_contents(MONGOS2_CONFIG_PATH, $config2);
    $config1 = file_get_contents(MONGOS1_CONFIG_PATH);
    $config2 = file_get_contents(MONGOS2_CONFIG_PATH);
    preg_match($pattern, $config1, $matches);
    $newPort1 = $matches[1];
    preg_match($pattern, $config2, $matches);
    $newPort2= $matches[1];
    if ($newPort1 != $port2 || $newPort2 != $port1) {
        throw new Exception("switch config port failed");
    }
    
    logMessage("restart mongos begin ...");
    stopMongos(getBackupMongos());
    stopMongos(getOfficialMongos());
    startMongos(getBackupMongos());
    startMongos(getOfficialMongos());
    logMessage("restart mongos end");
    
    logMessage("switch cluster role");
    $backupCluster = getBackupCluster();
    $officialCluster = getOfficialCluster();
    $info = loadClusterInfo($backupCluster);
    $info['role'] = 'official';
    saveClusterInfo($backupCluster, $info);
    $info = loadClusterInfo($officialCluster);
    $info['role'] = 'backup';
    saveClusterInfo($officialCluster, $info);
    $newBackupCluster = getBackupCluster();
    $newOfficialCluster = getOfficialCluster();
    if ($newBackupCluster != $officialCluster || $newOfficialCluster != $backupCluster) {
        throw new Exception("switch cluster role failed");
    }
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);
