<?php

namespace SNE;

/**
 * MySQL-Dumper Class to backup databases
 *
 * @author Lukas M. Beck <lb@1601.com>
 * @copyright 1601.production siegler&thuemmler ohg
 * @link https://github.com/LBeckX
 * @link https://www.1601.com
 */
class MYSQL_DUMP{

    /*
     * ********************************************************
     * Eigenschaften protected
     * ********************************************************
     */

    /**
     * The backup file path
     * @var string
     */
    protected $backupFolderPath = '';

    /**
     * The tmp file path
     * @var string
     */
    protected $tmpFolderPath = '';

    /**
     * The error log file path
     * @var string
     */
    protected $errorLogFilePath = '';

    /**
     * The dump shell order
     * @var string
     */
    protected $shellMySQLDump = 'mysqldump --host="%s" --user="%s" --password="%s" --default-character-set=utf8 --routines %s 2>> %s | gzip > %s';

    /**
     * The dump file [first string(Database name)],[second string(timestamp name)]
     * @var string
     */
    protected $dumpFile = '%s_%s_1Srv.sql.gz';

    /**
     * The tmp file name [first string(micro time)]
     * @var string
     */
    protected $tmpFile = 'mysqldump_%s_log.tmp';

     /*
      * ********************************************************
      * Methoden public
      * ********************************************************
      */

    /**
     * @param string $backupFolderPath (../backupDatabase)
     * @return $this|bool
     */
    public function setBackupFolderPath($backupFolderPath = ''){

        // Check if the passed parameter is a string
        if(!is_string($backupFolderPath)){
            $this->errorFunction("Backup file path must be a string");
        }

        // Set the backup file path to the global variable
        $this->backupFolderPath = $backupFolderPath;

        // Return the MYSQL_DUMP object
        return $this;
    }

    /**
     * @param string $tmpFolderPath (../backupDatabase/tmp)
     * @return $this|bool
     */
    public function setTmpFolderPath($tmpFolderPath = ''){

        // Check if the passed parameter is a string
        if(!is_string($tmpFolderPath)){
            $this->errorFunction("Tmp file path must be a string");
        }

        // Set the backup file path to the global variable
        $this->tmpFolderPath = $tmpFolderPath;

        // Return the MYSQL_DUMP object
        return $this;
    }

    /**
     * @param string $errorLogFilePath
     * @return $this|bool
     */
    public function setErrorLogFilePath($errorLogFilePath = ''){

        // Check if the passed parameter is a string
        if(!is_string($errorLogFilePath)){
            $this->errorFunction("Tmp file path must be a string");
        }

        if(strlen($errorLogFilePath) > 0){

            $pathInfo = pathinfo($errorLogFilePath);
            if($pathInfo['extension'] !== 'log'){
                $this->errorFunction('The filename must end with .log');
            }
        }

        // Set the backup file path to the global variable
        $this->errorLogFilePath = $errorLogFilePath;

        // Return the MYSQL_DUMP object
        return $this;
    }

    /**
     * Create a simple dump from database
     * @param string $host
     * @param string $dbUser
     * @param string $dbPassword
     * @param string $dbName
     * @return array
     */
    public function createSingleDBDump($host = 'localhost',$dbUser = 'root',$dbPassword = '',$dbName = ''){

        // Check if the db host is a string, otherwise the script dies with error message
        if(!is_string($host)){
            $this->errorFunction('db host must be a string');
        }

        // Check if the db user is a string, otherwise the script dies with error message
        if(!is_string($dbUser)){
            $this->errorFunction('db user must be a string');
        }

        // Check if the db password is a string, otherwise the script dies with error message
        if(!is_string($dbPassword)){
            $this->errorFunction('password must be a string');
        }

        // Check if the db name is a string, otherwise the script dies with error message
        if(!is_string($dbName)){
            $this->errorFunction('db name must be a string.');
        }

        // Call the dump method and create the backup
        $return = $this->mysqlDump($host,$dbUser,$dbPassword,$dbName);

        // If the return from dump is not an array, the script will die with an exception
        if(!is_array($return)){
            $this->errorFunction('An error has occurred. Can\' create the backup from database.',$dbName);
        }

        // Return the array
        return $return;
    }

    /**
     * Creates a database backup from the array information
     * @param array $dbInformationArray
     * @param string $dbInformationArray[*]['host']
     * @param string $dbInformationArray[*]['user']
     * @param string $dbInformationArray[*]['password'] optional
     * @param string $dbInformationArray[*]['name']
     * @return $this
     */
    public function createFullDBDump($dbInformationArray){

        // Run threw the array with the database information
        foreach ($dbInformationArray as $dbInformation){

            // Check if the host-index from array exists and is not empty, otherwise the script dies with error message
            if(!array_key_exists('host',$dbInformation) || empty($dbInformation['host'])){
                $this->errorFunction('Host is not set or is empty');
            }

            // Check if the user-index from array exists and is not empty, otherwise the script dies with error message
            if(!array_key_exists('user',$dbInformation) || empty($dbInformation['user'])){
                $this->errorFunction('DB user is not set or is empty');
            }

            if(!array_key_exists('password',$dbInformation)){
                $dbInformation['password'] = '';
            }

            // Check if the name-index from array exists and is not empty, otherwise the script dies with error message
            if(!array_key_exists('name',$dbInformation) || empty($dbInformation['name'])){
                $this->errorFunction('DB name is not set or is empty');
            }

            // Call the backup-method
            $return = $this->mysqlDump($dbInformation['host'],$dbInformation['user'],$dbInformation['password'],$dbInformation['name']);

            // If the return is no array, the script dies with an exception.
            if(!is_array($return)){
                $this->errorFunction('An error has occurred. Can\' create the backup from database.',$dbInformation['name']);
            }
        }

        return $this;
    }

    /*
     * ********************************************************
     * Methoden private
     * ********************************************************
     */

    /**
     * Perform the backup and return the result with time and file size;
     * @param $HOST
     * @param $DBUSER
     * @param $DBPASSWORD
     * @param $DBNAME
     * @return array;
     */
    private function mysqlDump($HOST,$DBUSER,$DBPASSWORD,$DBNAME){

        // create all required folder
        $this->createFolder();

        // Create the tmp file path string
        $tmpFile = $this->tmpFolderPath. '/' .sprintf($this->tmpFile,microtime(true));

        // Create the mysql dump file name
        $dumpFile = sprintf($this->dumpFile,$DBNAME,date("ymd-Hi"));

        // Create the mysql dump file path with file
        $dumpFilePath = $this->backupFolderPath . '/' . $dumpFile;

        // Create the tmp file for tmp for information
        if(!touch($tmpFile)) {$this->errorFunction('Can\'t create tmp-log-file');}

        // Create the dump command
        $cmd = sprintf($this->shellMySQLDump,$HOST,$DBUSER,$DBPASSWORD,$DBNAME,$tmpFile,$dumpFilePath);

        // Safe the begin time
        $backupBegin = microtime(true);

        // Execute the mysql dump
        $cmdResult = passthru($cmd);

        // Safe the begin time
        $backupEnd = microtime(true);

        // calculate the execute time
        $return['timeForBackup'] = round($backupEnd-$backupBegin, 2).' Sek.';

        // get the output from mysql dump out of tmp file
        $contentTmpLogfile = file_get_contents($tmpFile);

        // Get the output without a warning
        $return['log'] = str_replace("Warning: Using a password on the command line interface can be insecure.\n", '',$contentTmpLogfile);

        // delete the tmp file
        unlink($tmpFile);

        // get the filesize from mysql dump
        $dumpFileSize = filesize($dumpFilePath);

        // Create a human readable size format
        if ($dumpFileSize > 1024*1024) 		$return['fileSize'] = round($dumpFileSize/1024/1024, 2).' MB';
        elseif ($dumpFileSize > 1024) 		$return['fileSize'] = round($dumpFileSize/1024, 2).' KB';
        else 					 						$return['fileSize'] = $dumpFileSize .' Byte';

        // return all information
        return $return;
    }

    /**
     * Create the folder structure
     */
    private function createFolder(){

        // Check the backup file path. If its empty the default path is selected
        if(empty($this->backupFolderPath)){
            $this->backupFolderPath = __DIR__ . '/db_backup';
        }

        // Check the tmp file path. If its empty the default path is selected
        if(empty($this->tmpFolderPath)){
            $this->tmpFolderPath = $this->backupFolderPath . '/tmp';
        }

        // Check if the backup folder always exists
        if(!is_dir($this->backupFolderPath)){

            // If not it will be created
            if(!mkdir($this->backupFolderPath,0777,true)){
                $this->errorFunction('Can\'t create folder structure');
            }

            // If the folder always not exists, let the script die
            if(!is_dir($this->backupFolderPath)){
                $this->errorFunction('Can\'t create folder structure');
            }
        }

        // Check if the tmp folder always exists
        if(!is_dir($this->tmpFolderPath)){

            // Try to create the tmp folder
            if(!mkdir($this->tmpFolderPath,0777,true)){
                $this->errorFunction('Can\'t create tmp folder structure');
            }

            // If the folder always not exists, let the script die
            if(!is_dir($this->tmpFolderPath)){
                $this->errorFunction('Can\'t create tmp folder structure');
            }
        }
    }

    /**
     * @param string $string
     * @return string
     */
    private function escapeString($string){

        // If the given parameter is not a string, let the script die
        if(!is_string($string)){
            $this->errorFunction('$string must be a string!');
        }

        // Return a safe string
        return str_replace(['"','\\'],'',(strip_tags(trim($string))));
    }

    /**
     * Let the script die with a good error message and information
     * @param string $errorString
     */
    private function errorFunction($errorString,$information = false){

        if(!is_string($errorString)){
            die(__FUNCTION__.' - On line: '.__LINE__.'The error string must be a string!');
        }

        if(empty($this->errorLogFilePath)){
            if($this->backupFolderPath !== ''){
                $this->errorLogFilePath = $this->backupFolderPath.'/mysql_error.log';
            } else {
                $this->errorLogFilePath = 'mysql_error.log';
            }
        }

        if(!file_exists($this->errorLogFilePath)){
            if(!touch($this->errorLogFilePath)){
                die(__FUNCTION__.' - On line: '.__LINE__.'Cant create error log file!');
            }
        }

        $errorString = __FUNCTION__.' - On line: '.__LINE__. ' - Error message: ' . $this->escapeString($errorString);

        if(is_string($information) && !empty($information && $information !== false)) {
            $errorString = $errorString . ' - Informations: ' . $this->escapeString($information);
            file_put_contents($this->errorLogFilePath,$errorString);
            die($errorString);
        }
        elseif (is_array($information) && !empty($information) && $information !== false) {
            $errorString = $errorString . ' - Informations: ' . $this->escapeString(print_r($information,true));
            file_put_contents($this->errorLogFilePath,$errorString);
            die($errorString);
        }
        else{
            file_put_contents($this->errorLogFilePath,$errorString);
            die($errorString);
        }

    }
}