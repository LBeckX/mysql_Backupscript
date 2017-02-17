<?php

require_once __DIR__ . '/../3rd-party/RP2-Framework/bootstrap.php';

$mySqlBackup = new \rpf\extension\module\mySqlBackup();

$databases = $mySqlBackup->getDatabaseInformations();
