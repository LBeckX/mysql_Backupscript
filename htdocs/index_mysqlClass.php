<?php



/****************************************************************************************************
 *********************************************OLD****************************************************
 ****************************************************************************************************/



//require_once __DIR__ . '/../3rd-party/rp2_framework/bootstrap.php';
//require_once __DIR__ . '/../config/rp2_conf.inc.php';
//require_once __DIR__ . '/../class/log.class.php';
//require_once __DIR__ . '/../class/mysqlDump.class.php';


//$mysqlInformationArray = getAllDatabasesFromRp2(URL,USER,PWD);
//performBackup($mysqlInformationArray);


/**
 * Get all mysql databases from rp2
 * @param string $rp2Url
 * @param  string $rp2User
 * @param $rp2Pwd
 * @return array|bool
 */
/*function getAllDatabasesFromRp2($rp2Url,$rp2User,$rp2Pwd){
        $rpf = new \rpf\system\rpf();

        $rpf->getApi()
            ->getUser()
            ->auth($rp2Url,$rp2User,$rp2Pwd);

        $mysqlInformationArray = $rpf->getApi()
            ->getMysqlReadEntry()
            ->get();

        return $mysqlInformationArray;
    }*/

/**
 * Create a backup of 21 databases
 * @param array $mysqlInformationArray
 */
/*function performBackup($mysqlInformationArray){
        if(!is_array($mysqlInformationArray)){
            die('Keine Rückgabe');
        }

        $LOG = new SNE\LOG();
        $MYSQLDUMP = new SNE\MYSQL_DUMP();
        $LOG->addMessage('---------- Starting MySQL-Dump '.date('d.m.Y H:i:s').' ----------');

        $MYSQLDUMP->setBackupFolderPath(__DIR__ . '/backupDatabase');

        for($i=0;$i<=9;$i++){
            $dbHost = $mysqlInformationArray[$i]['hostip'];
            $dbUser = $mysqlInformationArray[$i]['pk'];
            $dbPassword = $mysqlInformationArray[$i]['password'];
            $dbName = $mysqlInformationArray[$i]['name'];


            $result = $MYSQLDUMP->createSingleDBDump($dbHost,$dbUser,$dbPassword,$dbName);
            //$result = $MYSQLDUMP->createSingleDBDump($dbHost,$dbUser,,$dbName);
            $LOG->addMessage($result);
        }

        $LOG->addMessage('---------- Ending MySQL-Dump '.date('d.m.Y H:i:s').' ----------');
        $LOG->safeAsFile('../htdocs/log.log');
    }*/