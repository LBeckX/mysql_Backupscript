<?php
header("Content-Type: text/html; charset=utf-8");

require_once __DIR__ . '/../3rd-party/RP2-Framework/bootstrap.php';

$mySqlBackup = new \rpf\extension\module\mySqlBackup();

$mySqlBackup->setBackupFolderPath(__DIR__."/backups_tmp");

$databases = $mySqlBackup->getDatabaseInformations();

$mySqlBackup->createFullBackup($databases);

foreach ($databases as $database){
    $mySqlBackup->createSingleBackup($database['hostip'],$database['pk'],$database['password'],$database['name']);
}

$mySqlBackup->sendProtocol("lb@1601.com");

