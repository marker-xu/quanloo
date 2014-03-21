#!/home/worker/php5/bin/php
<?php

require_once dirname(__FILE__).'/common.php';

try {
    logMessage("stop backup cluster begin ...");
    $script = "/home/worker/video-cluster-admin/init-backup-cluster/stop_backup_cluster.php";
    execScriptOnRemoteServers(getClusterServers(), $script);
    logMessage("stop backup cluster end");
    
    logMessage("init backup cluster dir begin ...");
    $script = "/home/worker/video-cluster-admin/init-backup-cluster/init_backup_cluster_dir.php";
    execScriptOnRemoteServers(getClusterServers(), $script);
    logMessage("init backup cluster dir end");
    
    logMessage("start backup cluster begin ...");
    $script = "/home/worker/video-cluster-admin/init-backup-cluster/start_backup_cluster.php";
    execScriptOnRemoteServers(getClusterServers(), $script);
    logMessage("start backup cluster end");
    
    logMessage("check backup cluster status begin ...");
    checkClusterStatus(getBackupMongosAddresses());
    logMessage("check backup cluster status end");
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);

