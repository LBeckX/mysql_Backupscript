<?php

namespace rpf\extension\module;
use rpf\extension\extensionModule;
use rpf\system\module\log;

require_once __DIR__ . '/../extensionModule.php';

/**
 * Class mySqlBackup
 * @package rpf\extension
 *
 * @author Lukas M. Beck <lb@1601.com>
 * @copyright 1601.communication gmbh
 * @link https://github.com/LBeckX
 * @link http://www.unitgreen.com
 * @link https://www.1601.com
 */
class mySqlBackup extends extensionModule {

    /**
     * Contains the complete protocol after mysql dump
     * @var array $protocol
     */
    protected $protocol = [];

    /**
     * The backup file path
     * @var string
     */
    protected $backupFolderPath = '';

    /**
     * The dump file [string(Database name)],[string(timestamp name)]
     * @var string $dumpFile
     */
    protected $dumpFile = '%s_%s_1Srv.sql.gz';

    /**
     * The tmp file name [first string(micro time)]
     * @var string
     */
    protected $tmpFile = 'mysqldump_%s_log.tmp';

    /**
     * The dump shell order
     * @var string
     */
    private $shellMySQLDump = 'mysqldump --host="%s" --user="%s" --password="%s" --default-character-set=utf8 --routines %s 2>> %s | gzip > %s';

    /**
     * Set the backup folder path. The directory where backups are stored.
     * @param $backupFolderPath
     * @return $this
     */
    public function setBackupFolderPath($backupFolderPath){
        // Check if the passed parameter is a string
        if(!is_string($backupFolderPath)){
            log::warning("Backup file path must be a string",__METHOD__);
            return $this;
        }

        // Set the backup file path to the global variable
        $this->backupFolderPath = $backupFolderPath;

        // Return the MYSQL_DUMP object
        return $this;
    }

    /**
     * Return all database information from RP2
     * @return array|bool
     */
    public function getDatabaseInformations(){
        $mysqlDatabases = $this
            ->getApi()
            ->getMysqlReadEntry()
            ->getArray();
        return $mysqlDatabases;
    }

    /**
     * Create a simple dump from database
     * @param string $host
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbName
     * @return $this
     * @throws \Exception
     */
    public function createSingleBackup($host = 'localhost',$dbUser = 'root',$dbPassword = '',$dbName = ''){
        // Check if the db host is a string, otherwise the script dies with error message
        if(!is_string($host)){
            log::error('DB host must be a string',__METHOD__);
            throw new \Exception('DB host must be a string in: '.__METHOD__);
        }

        // Check if the db user is a string, otherwise the script dies with error message
        if(!is_string($dbUser)){
            log::error('DB user must be a string',__METHOD__);
            throw new \Exception('DB user must be a string in: '.__METHOD__);
        }

        // Check if the db password is a string, otherwise the script dies with error message
        if(!is_string($dbPassword)){
            log::error('Password must be a string',__METHOD__);
            throw new \Exception('DB password must be a string in: '.__METHOD__);
        }

        // Check if the db name is a string, otherwise the script dies with error message
        if(!is_string($dbName)){
            log::error('DB name must be a string',__METHOD__);
            throw new \Exception('DB name must be a string in: '.__METHOD__);
        }

        // Call the dump method and create the backup
        if(!$this->mysqlDump($host,$dbUser,$dbPassword,$dbName)){
            log::error('Mysql backup was not correct. Pls check the rpf log!',__METHOD__);
            throw new \Exception('Mysql backup was not correct. Pls check the rpf log!');
        }

        // Return the object
        return $this;
    }

    /**
     * Creates a database backup from the array information
     * @param $dbInformationArray
     * @return $this
     * @throws \Exception
     */
    public function createFullBackup($dbInformationArray){
        // Run threw the array with the database information
        foreach ($dbInformationArray as $dbInformation){

            // Check if the host-index from array exists and is not empty, otherwise the script dies with error message
            if(array_key_exists('host',$dbInformation) && !empty($dbInformation['host'])){
                $host = $dbInformation['host'];
            }
            elseif(array_key_exists('hostip',$dbInformation) && !empty($dbInformation['hostip'])){
                $host = $dbInformation['hostip'];
            }
            else{
                log::error('Host is not set or is empty',__METHOD__);
                throw new \Exception('DB host is not set or is empty in: '.__METHOD__);
            }

            // Check if the user-index from array exists and is not empty, otherwise the script dies with error message
            if(array_key_exists('user',$dbInformation) && !empty($dbInformation['user'])){
                $user = $dbInformation['user'];
            }
            elseif (array_key_exists('pk',$dbInformation) && !empty($dbInformation['pk'])){
                $user = $dbInformation['pk'];
            }
            else{
                log::error('DB user is not set or is empty',__METHOD__);
                throw new \Exception('DB user is not set or is empty in: '.__METHOD__);
            }

            if(!array_key_exists('password',$dbInformation)){
                $password = '';
            }
            else{
                $password = $dbInformation['password'];
            }

            // Check if the name-index from array exists and is not empty, otherwise the script dies with error message
            if(array_key_exists('name',$dbInformation) && !empty($dbInformation['name'])){
                $name = $dbInformation['name'];
            }
            else{
                log::error('DB name is not set or is empty',__METHOD__);
                throw new \Exception('DB name is not set or is empty in: '.__METHOD__);
            }

            // Call the dump method and create the backup
            if(!$this->mysqlDump($host,$user,$password,$name)){
                log::error('Mysql backup was not correct. Pls check the rpf log!',__METHOD__);
                throw new \Exception('Mysql backup was not correct. Pls check the rpf log!');
            }
        }

        // return the object
        return $this;
    }

    /**
     * Send the protocol as email
     * @param string $email
     * @param string $subject
     * @return bool
     */
    public function sendProtocol($email = '',$subject = '[RPF] MySQL dump protocol'){

        if(empty($this->protocol)){
            log::notice('The protocol is empty',__METHOD__);
        }

        if(!is_string($email)){
            log::notice('The e-mail must be a string',__METHOD__);
            return false;
        }

        if(empty($email)){
            log::notice('The e-mail parameter is empty',__METHOD__);
            return false;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            log::notice('The e-mail format is not correct',__METHOD__);
            return false;
        }

        if(!is_string($subject)){
            log::notice('The subject must be a string',__METHOD__);
            return false;
        }

        if(!mail($email,$subject,print_r($this->protocol,true))){
            log::notice('The protocol could not be delivered',__METHOD__);
            return false;
        }

        return true;
    }

    /**
     * Return the protocol
     * @return array $this->protocol
     */
    public function getProtocol(){

        if(empty($this->protocol)){
            log::notice('The protocol is empty',__METHOD__);
        }

        return $this->protocol;
    }

    /**
     * Clear the protocol with the dump information
     * @return bool
     */
    public function clearProtocol(){
        $this->protocol = [];
        return true;
    }

    /**
     * Perform the backup and return the result with time and file size;
     * @param string $host
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbName
     * @return mixed
     */
    protected function mysqlDump($host = 'localhost',$dbUser = 'root',$dbPassword = '',$dbName = ''){

        // Init the log array;
        $log = [];

        // Create the backup folder
        $this->createBackupFolder();

        // Create the mysql dump file name
        $dumpFileName = sprintf($this->dumpFile,$dbName,date("ymd-His"));

        // Create the mysql dump file path
        $dumpFilePath = $this->backupFolderPath . '/' . $dumpFileName;

        // Create the tmp file path
        $tempFilePath = $this->backupFolderPath . '/' . sprintf($this->tmpFile,microtime(true));

        // Create the tmp file for the dump information
        if(!touch($tempFilePath)) {
            log::error('Can\'t create the tmp log file',__METHOD__);
        }

        // Create the dump command for shell
        $cmd = sprintf($this->shellMySQLDump,$host,$dbUser,$dbPassword,$dbName,$tempFilePath,$dumpFilePath);

        // Safe the begin time
        $backupBegin = microtime(true);

        // Execute the mysql dump
        $cmdResult = passthru($cmd);

        // Safe the begin time
        $backupEnd = microtime(true);

        // calculate the execute time
        $log['timeForBackup'] = round($backupEnd-$backupBegin, 2).' Sek.';

        // get the output from mysql dump out of tmp file
        $contentTmpLogfile = file_get_contents($tempFilePath);

        // Get the output without a warning
        $log['log'] = str_replace("Warning: Using a password on the command line interface can be insecure.\n", '',$contentTmpLogfile);

        // delete the tmp file
        unlink($tempFilePath);

        // get the filesize from mysql dump
        $dumpFileSize = filesize($dumpFilePath);

        // Create a human readable size format
        if ($dumpFileSize > 1024*1024) 		$log['fileSize'] = round($dumpFileSize/1024/1024, 2).' MB';
        elseif ($dumpFileSize > 1024) 		$log['fileSize'] = round($dumpFileSize/1024, 2).' KB';
        else 					 			$log['fileSize'] = $dumpFileSize .' Byte';

        // If the return is no array, the script dies with an exception.
        if(!is_array($log)){
            log::error('An error has occurred. Can\'t create the backup from database.',$dbName);
            return false;
        }

        // Add a new entry to the log array
        $this->protocol[$dbName] = $log;

        // return all information about the dump
        return true;
    }

    /**
     * Create the backup folder
     * @return bool
     * @throws \Exception
     */
    private function createBackupFolder(){

        // Check the backup file path. If it is empty the default path is selected
        if(empty($this->backupFolderPath)){
            $this->backupFolderPath = __DIR__ . '/db_backup';
            log::notice('The MySQL-dump path is the default path:',__DIR__ . '/db_backup');
        }

        // Check if the backup folder always exists
        if(!is_dir($this->backupFolderPath)){

            // If not it will be created
            if(!mkdir($this->backupFolderPath,0777,true)){
                log::error('Can\'t create folder structure',__METHOD__);
                throw new \Exception('Can\'t create folder structure in: '.__METHOD__);
            }

            // If the folder always not exists, let the script die
            if(!is_dir($this->backupFolderPath)){
                log::error('Can\'t create folder structure',__METHOD__);
                throw new \Exception('Can\'t create folder structure in: '.__METHOD__);
            }
        }

        // Create gitignore in backup folder and exclude all db backups
        file_put_contents($this->backupFolderPath."/.gitignore","db*");

        return true;
    }
}