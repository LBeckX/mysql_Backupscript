<?php

namespace SNE;

/**
 * This class is for simple log operations
 *
 * Class LOG
 * @package SNE
 *
 * @author Lukas M. Beck
 * @link https://1601.com
 */
class LOG{

    private $message = [];

    /**
     * Add a message to the message array element. If $messageIndexKey is an int or an string, the
     * message has this index
     * @param $messageString
     * @param null $messageIndexKey
     * @return bool
     */
    public function addMessage($messageString,$messageIndexKey = null){

        if(!is_string($messageString) && !is_array($messageString)){
           echo 'Message must be a sting or an array';
           return false;
        }

        if($messageIndexKey !== null){
            if(!is_int($messageIndexKey) && !is_string($messageIndexKey)){
                echo 'The messagearray key must be an integer or string';
                return false;
            }

            $this->message[$messageIndexKey] = $messageString;
            return true;
        }
        else {
            array_push($this->message,$messageString);
            return true;
        }
    }

    /**
     * Return the message element as an array
     * @return array
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * Return an message element by index
     * @param $indexKey
     * @return bool|mixed
     */
    public function getMessageByIndexKey($indexKey){
        if(!is_int($indexKey) && !is_string($indexKey)){
            echo 'The message index key must be a string or integer';
            return false;
        }

        if(array_key_exists($indexKey,$this->message)){
            return $this->message[$indexKey];
        }
        else{
            return false;
        }
    }

    /**
     * Delete the whole message element
     */
    public function deleteMessage(){
        unset($this->message);
    }

    /**
     * Delete only one message element by key
     * @param $messageElementKey
     * @return bool
     */
    public function deleteMessageElementByKey($messageElementKey){
        if(!is_int($messageElementKey) && !is_string($messageElementKey)){
            echo 'The message array key must be a string or integer to unset the element';
            return false;
        }

        unset($this->message[$messageElementKey]);
        return true;
    }

    /**
     * Send the actual message as mail
     * @param $email
     * @param $subject
     * @return bool
     */
    public function sendMessageMail($email,$subject){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo "the email address is invalid";
            return false;
        }

        if(!is_string($subject)){
            echo "The subject must be a string";
            return false;
        }

        return mail($email,trim($subject),implode('\r\n',$this->message));
    }

    /**
     * Safe the actual message array into a file with the extension .log
     * @param $pathAndFile
     * @return bool
     */
    public function safeAsFile($pathAndFile){
        if(!is_string($pathAndFile)){
            echo "Path and file must be a string!";
            return false;
        }

        $pathInfo = pathinfo($pathAndFile);
        if($pathInfo['extension'] !== 'log'){
            echo "the filename must end with .log";
            return false;
        }

        if(!file_exists($pathAndFile)){
            if(!touch($pathAndFile)){
                echo "can not create the file:".$pathAndFile.'<br>pls. check the dir.';
                return false;
            }

            if(!file_exists($pathAndFile)){
                echo "the file always not exists";
                return false;
            }
        }

        if(file_put_contents($pathAndFile,print_r($this->message,true),FILE_APPEND) === false){
            echo "Can't write data into file";
            return false;
        }

        return true;
    }
}