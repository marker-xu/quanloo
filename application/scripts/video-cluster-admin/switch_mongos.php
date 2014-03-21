#!/home/worker/php5/bin/php
<?php

require_once dirname(__FILE__).'/common.php';

try {
    logMessage("check cluster consistency");
    checkClusterConsistency();

    logMessage("switch mongos begin ...");
    $script = "/home/worker/video-cluster-admin/switch-mongos/switch_mongos.php";
    execScriptOnRemoteServers(getMongosServers(), $script, false);
    logMessage("switch mongos end");
    
    logMessage("check official cluster");
    checkClusterStatus(getOfficialMongosAddresses(), 10);
    
    logMessage("check http status");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $urls = array(
//        'http://www.quanloo.com/',
    );
    foreach ($urls as $url) {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            curl_close($ch);
            throw new Exception("request url '$url' failed, code - $code");
        }
    }
    curl_close($ch);
    
    logMessage("stop backup cluster begin ...");
    $script = "/home/worker/video-cluster-admin/init-backup-cluster/stop_backup_cluster.php";
    execScriptOnRemoteServers(getClusterServers(), $script);
    logMessage("stop backup cluster end");
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);