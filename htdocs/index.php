<?php
    require_once __DIR__ . '/../3rd-party/rp2-framework/bootstrap.php';
    require_once __DIR__ . '/../config/config.php';
    $rpf = new \rpf\system\rpf();


    $rpf
        ->getApi()
        ->getUser()
        ->auth(URL,UNAME,UPASSWD);

    $rpf
        ->getExtension()
        ->getMysqlExport()
        ->buildList();
?>
