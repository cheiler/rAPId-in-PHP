<?php

/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 04/06/2017
 * Time: 18:52
 */
class rapidapi
{
    private $apiKey="test";
    private $sharedSecret="test";


    function __construct(){
        $this->debug = true;
    }

    /**
     * @name debugger
     * @param $msg
     * @internal
     */
    private function debugger($msg)
    {
        if ($this->debug){
            if($this->debug){
                $msg = str_replace('"', '\\"', $msg);
                echo "<script>console.log(\"$msg\")</script>";

            }

        }

    }

    public function getAuthHeader(){
        $epoch = time();
        $this->debugger("Timestamp: $epoch");
        $toEncode = $this->apiKey + $this->sharedSecret + $epoch;
        $this->debugger("String to Encode: $toEncode");
        $hash = hash('sha512',"$toEncode");
        $this->debugger("Hash: $hash");
        $auth_header_string = "EAN APIKey=". $this->apiKey . ",Signature=" . $hash . ",timestamp=" . $epoch;
        $this->debugger("Authentication Header: $auth_header_string");
        return $auth_header_string;
    }




    /**Getters and Setters

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     * @return rapidapi
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->sharedSecret;
    }

    /**
     * @param string $sharedSecret
     * @return rapidapi
     */
    public function setSharedSecret($sharedSecret)
    {
        $this->sharedSecret = $sharedSecret;
        return $this;
    }





}