#!/home/worker/php5/bin/php
<?php

require_once dirname(__FILE__).'/../common.php';

try {
    logMessage("begin sleep ...");
    sleep(5);
    logMessage("end sleep");
} catch (Exception $e) {
    logMessage($e->getMessage(), "ERROR");
    exit(1);
}
exit(0);

