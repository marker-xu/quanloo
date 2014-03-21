<?php

define('CLUSTER1_DIR', '/home/worker/mdbcluster1');
define('CLUSTER2_DIR', '/home/worker/mdbcluster2');

define('CLUSTER_INFO_FILENAME', 'running_info.json');

define('CLUSTER1_PACKAGE_PATH', '/home/worker/mdbcluster1.tar.gz');
define('CLUSTER2_PACKAGE_PATH', '/home/worker/mdbcluster2.tar.gz');

define('MONGOS_PATH', '/home/worker/mongodb/bin/mongos');

define('MONGOS1_CONFIG_PATH', '/home/worker/mongodb/etc/video1-mongos.cnf');
define('MONGOS2_CONFIG_PATH', '/home/worker/mongodb/etc/video2-mongos.cnf');

define('MONGOS1_PIDFILE_PATH', '/home/worker/data/mongodb/video1-mongos/mongos.pid');
define('MONGOS2_PIDFILE_PATH', '/home/worker/data/mongodb/video2-mongos/mongos.pid');

function getClusterServers()
{
    return array(
        'videodb1',
        'videodb2',
        'videodb3',
        'videodb4',
    );
}

function getMongosServers()
{
    return array(
        'videodb1',
        'videodb2',
        'videodb3',
        'videodb4',
    );
}

function getBackupMongosAddresses()
{
    return array(
        '10.156.24.76:30010',
        '10.156.24.116:30010',
        '10.156.24.109:30010',
        '10.156.24.111:30010',
    );
}

function getOfficialMongosAddresses()
{
    return array(
        '10.156.24.76:30000',
        '10.156.24.116:30000',
        '10.156.24.109:30000',
        '10.156.24.111:30000',
    );
}

function getBackupMongos()
{
    $clusters = array(1, 2);
    $found = array();
    foreach ($clusters as $cluster) {
        $config = file_get_contents(constant("MONGOS{$cluster}_CONFIG_PATH"));
        if (preg_match('/port = (\d+)/m', $config, $matches)) {
            $address = getLocalIp().":".$matches[1];
            if (in_array($address, getBackupMongosAddresses())) {
                $found[] = $cluster;
            }
        }
    }
    if (count($found) != 1) {
        throw new Exception("found ".count($found)." backup mongos");
    }
    return current($found);
}

function getOfficialMongos()
{
    $clusters = array(1, 2);
    $found = array();
    foreach ($clusters as $cluster) {
        $config = file_get_contents(constant("MONGOS{$cluster}_CONFIG_PATH"));
        if (preg_match('/port = (\d+)/m', $config, $matches)) {
            $address = getLocalIp().":".$matches[1];
            if (in_array($address, getOfficialMongosAddresses())) {
                $found[] = $cluster;
            }
        }
    }
    if (count($found) != 1) {
        throw new Exception("found ".count($found)." official mongos");
    }
    return current($found);
}

function getBackupCluster()
{
    $clusters = array(1, 2);
    $found = array();
    foreach ($clusters as $cluster) {
        $info = loadClusterInfo($cluster);
        if ($info && isset($info['role']) && $info['role'] == 'backup') {
            $found[] = $cluster;
        }
    }
    if (count($found) != 1) {
        throw new Exception("found ".count($found)." backup clusters");
    }
    return current($found);
}

function getOfficialCluster()
{
    $clusters = array(1, 2);
    $found = array();
    foreach ($clusters as $cluster) {
        $info = loadClusterInfo($cluster);
        if ($info && isset($info['role']) && $info['role'] == 'official') {
            $found[] = $cluster;
        }
    }
    if (count($found) != 1) {
        throw new Exception("found ".count($found)." official clusters");
    }
    return current($found);
}

function getClusterDir($cluster)
{
    if ($cluster == 1) {
        return CLUSTER1_DIR;
    } else if ($cluster == 2) {
        return CLUSTER2_DIR;
    } else {
        throw new Exception("cluster $cluster not exists");
    }
}

function getClusterPackagePath($cluster)
{
    if ($cluster == 1) {
        return CLUSTER1_PACKAGE_PATH;
    } else if ($cluster == 2) {
        return CLUSTER2_PACKAGE_PATH;
    } else {
        throw new Exception("cluster $cluster not exists");
    }
}

function getClusterInfoPath($cluster)
{
    $clusterDir = getClusterDir($cluster);
    return $clusterDir.'/'.CLUSTER_INFO_FILENAME;
}

function saveClusterInfo($cluster, $info)
{
    if (!is_array($info)) {
        throw new Exception("info must be an array");
    }
    if (!file_put_contents(getClusterInfoPath($cluster), json_encode($info))) {
        throw new Exception("save running info failed");
    }
}

function loadClusterInfo($cluster)
{
    $infoPath = getClusterInfoPath($cluster);
    if (file_exists($infoPath)) {
        $content = file_get_contents($infoPath);
        $content = json_decode($content, true);
        if (!is_array($content)) {
            return false;
        } else {
            return $content;
        }
    } else {
        return false;
    }
}

function getClusterPortList($cluster)
{
    $content = file_get_contents(getClusterDir($cluster)."/port.mdbcluster".$cluster);
    if (!$content) {
        throw new Exception("get port list of cluster $cluster failed");
    }
    $ports = explode("\n", trim($content));
    foreach ($ports as &$port) {
        $port = trim($port);
    }
    return $ports;
}

function getClusterStartScriptPath($cluster)
{
    $path = getClusterDir($cluster)."/".getMachineName()."/start_all.sh";
    if (!file_exists($path)) {
        throw new Exception("cluster $cluster start script $path not exists");
    }
    return $path;
}

function getClusterStopScriptPath($cluster)
{
    $path = getClusterDir($cluster)."/".getMachineName()."/stop_all.sh";
    if (!file_exists($path)) {
        throw new Exception("cluster $cluster stop script $path not exists");
    }
    return $path;
}

function getMachineName()
{
    $name = trim(shell_exec("uname -n"));
    if (!preg_match('/^videodb\d+\.sii\.cn$/', $name)) {
        throw new Exception("get machine name failed");
    }
    return $name;
}

function logMessage($message, $level = "DEBUG")
{
    $message = "[".str_pad(getLocalIp(), 15, ' ', STR_PAD_RIGHT)."] [".date('Y-m-d H:i:s')."] ["
        .str_pad(strtoupper($level), 5, ' ', STR_PAD_RIGHT)."] $message\n";
    echo $message;
    $dir = dirname(__FILE__)."/log";
    if (!is_dir($dir)) {
        mkdir($dir, 0755);
    }
    $filename = $dir."/".date('Ymd').".log";
    file_put_contents($filename, $message, FILE_APPEND);
}

function getLocalIp()
{
    $output = shell_exec("/sbin/ifconfig eth0");
    if (!preg_match('/inet addr:([\d\.]+)/m', $output, $mathes)) {
        throw new Exception("get local ip failed");
    }
    return $mathes[1];
}

function waitLocalPortClose($ports, $times = 1, $interval = 1)
{
    $closed = array();
    for ($i = 0; $i < $times; $i++) {
        logMessage(json_encode($ports));
        $output = shell_exec("netstat -lt");
        foreach ($ports as $port) {
            if (strpos($output, ":$port") === false) {
                $closed[] = $port;
            }
        }
        $ports = array_values(array_diff($ports, $closed));
        if (!$ports) {
            return;
        } else {
            sleep($interval);
        }
    }
    throw new Exception("wait local port close failed, ports - ".json_encode($ports));
}

function waitLocalPortOpen($ports, $times = 1, $interval = 1)
{
    $opened = array();
    for ($i = 0; $i < $times; $i++) {
        logMessage(json_encode($ports));
        $output = shell_exec("netstat -lt");
        foreach ($ports as $port) {
            if (strpos($output, ":$port") !== false) {
                $opened[] = $port;
            }
        }
        $ports = array_values(array_diff($ports, $opened));
        if (!$ports) {
            return;
        } else {
            sleep($interval);
        }
    }
    throw new Exception("wait local port open failed, ports - ".json_encode($ports));
}

function waitProcessStart($pids, $times = 1, $interval = 1)
{
    $started = array();
    for ($i = 0; $i < $times; $i++) {
        logMessage(json_encode($pids));
        foreach ($pids as $pid) {
            $output = shell_exec("ps -p $pid");
            if (count(explode("\n", trim($output))) == 2) {
                $started[] = $pid;
            }
        }
        $pids = array_values(array_diff($pids, $started));
        if (!$pids) {
            return;
        } else {
            sleep($interval);
        }
    }
    throw new Exception("wait process start failed, pids - ".json_encode($pids));
}

function waitProcessExit($pids, $times = 1, $interval = 1)
{
    $exited = array();
    for ($i = 0; $i < $times; $i++) {
        logMessage(json_encode($pids));
        foreach ($pids as $pid) {
            $output = shell_exec("ps -p $pid");
            if (count(explode("\n", trim($output))) == 1) {
                $exited[] = $pid;
            }
        }
        $pids = array_values(array_diff($pids, $exited));
        if (!$pids) {
            return;
        } else {
            sleep($interval);
        }
    }
    throw new Exception("wait process exit failed, pids - ".json_encode($pids));
}

function execScriptOnRemoteServers($servers, $script, $parallel = TRUE)
{
    if ($parallel) {
        $pids = array();
        foreach ($servers as $server) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Exception('fork process failed');
            } else if ($pid > 0) {
                $pids[$pid] = $server;
            } else {
                $cmd = "ssh $server $script";
                $output = array();
                $ret = 1;
                logMessage($cmd);
                exec($cmd, $output, $ret);
                logMessage("\n".str_repeat('*', 50)."\n".implode("\n", $output)."\n".str_repeat('*', 50));
                exit($ret);
            }
        }
        while (true) {
            sleep(1);
            $pid = pcntl_waitpid(-1, $status, WNOHANG);
            if ($pid == -1) {
                throw new Exception("wait child failed");
            } else if ($pid > 0) {
                if (pcntl_wifexited($status)) {
                    $ret = pcntl_wexitstatus($status);
                    if ($ret == 0) {
                        logMessage("child $pid exec ok, server - ".$pids[$pid]);
                        unset($pids[$pid]);
                    } else {
                        throw new Exception("child $pid exec failed, ret - $ret, server - ".$pids[$pid]);
                    }
                } else {
                    throw new Exception("child $pid was interrupted, server - ".$pids[$pid]);
                }
            }
            if (!$pids) {
                break;
            }
        }
    } else {
        foreach ($servers as $server) {
            $cmd = "ssh $server $script";
            $output = array();
            $ret = 1;
            logMessage($cmd);
            exec($cmd, $output, $ret);
            logMessage("\n".str_repeat('*', 50)."\n".implode("\n", $output)."\n".str_repeat('*', 50));
            if ($ret != 0) {
                throw new Exception("exec '$cmd' on '$server' failed, ret - $ret");
            }
        }
    }
}

function getDiskSizeAvailable()
{
    $output = shell_exec("df /home/");
    preg_match('/\s\d+\s\d+\s(\d+)\s/m', $output, $matches);
    $available = (int) $matches[1];
    if ($available <= 0) {
        throw new Exception("get disk size available failed");
    }
    return $available;
}

function getClusterDiskSize($cluster)
{
    $output = shell_exec("du -c --max-depth=1 ".getClusterDir($cluster));
    $line = array_pop(explode("\n", trim($output)));
    $columns = explode(' ', $line);
    $size = (int) $columns[0];
    if ($size <= 0) {
        throw new Exception("get cluster $cluster disk size failed");
    }
    return $size;
}

function checkDiskSize()
{
    $available = getDiskSizeAvailable();
    $size = (int) getClusterDiskSize(getOfficialCluster()) * 1.2;
    if ($available < $size) {
        throw new Exception("disk only $available available, need $size");
    }
}

function checkClusterStatus($mongoses, $times = 1, $interval = 1)
{
    for ($i = 0; $i < $times; $i++) {
        $ok = true;
        foreach ($mongoses as $mongos) {
            logMessage($mongos);
            $result = array();
            try {
                $mongo = new Mongo($mongos);
                $db = $mongo->admin;
                $result = $db->command(array('listshards' => 1));
            } catch (MongoException $e) {
                logMessage($e->getMessage, 'WARN');
            }
            if (!isset($result['shards']) || count($result['shards']) != 8) {
                $ok = false;
                break;
            }
        }
        if ($ok) {
            return;
        } else {
            sleep($interval);
        }
    }
    throw new Exception("check cluster status failed, mongos - $mongos, ret - ".json_encode($result));
}

function checkClusterConsistency()
{
    $backupMongos = array_shift(getBackupMongosAddresses());
    $officialMongos = array_shift(getOfficialMongosAddresses());
    $mongo = new Mongo($backupMongos);
    $db = $mongo->video_search;
    $coll = $db->video;
    $backupCount = $coll->count();
    $mongo = new Mongo($officialMongos);
    $db = $mongo->video_search;
    $coll = $db->video;
    $officialCount = $coll->count();
    if (abs($backupCount - $officialCount) / $officialCount > 0.2) {
        throw new Exception("difference between backup cluster count and official cluster count is too large, backup - $backupCount, official - $officialCount");
    }
}

function stopMongos($cluster)
{
    $pids = array();
    $pid = (int) trim(file_get_contents(constant("MONGOS{$cluster}_PIDFILE_PATH")));
    if ($pid > 0) {
        $cmd = "kill $pid";
        shell_exec($cmd);
        $pids[] = $pid;
    }
    waitProcessExit($pids, 10);
}

function startMongos($cluster)
{    
    $cmd = MONGOS_PATH." -f ".constant("MONGOS{$cluster}_CONFIG_PATH");
    shell_exec($cmd);
    $pid = 0;
    $i = 0;
    while (true) {
        sleep(1);
        $pid = (int) trim(file_get_contents(constant("MONGOS{$cluster}_PIDFILE_PATH")));
        if ($pid > 0) {
            break;
        }
        if (++$i == 10) {
            throw new Exception("start mongos failed, cluster - $cluster");
        }
    }
}
