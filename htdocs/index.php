<?php
header("Content-Type: text/html; charset=utf-8");
require_once __DIR__ . '/../RP2-Framework/bootstrap.php';

$backupPath = __DIR__."/backups_tmp/".date("Y")."/".date("m")."/".date("d");

$mySqlBackup = new \rpf\extension\module\mySqlBackup();

$mySqlBackup->setBackupFolderPath($backupPath);

$databases = $mySqlBackup->getDatabaseInformations();

$mySqlBackup->createFullBackup($databases);

file_put_contents($backupPath."/log.txt",print_r($mySqlBackup->getProtocol(),true));

$mySqlBackup->sendProtocol("lb@1601.com");