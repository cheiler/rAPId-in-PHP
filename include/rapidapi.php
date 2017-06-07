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
        print("Hello");
    }

    /**
     * @name debugger
     * @param $msg
     * @internal
     */
    private function debugger($msg)
    {
        if ($this->debug){
            $msg = str_replace('"', '\\"', $msg);
            echo "<script>console.log(\"$msg\")</script>";
        }

    }

    public function getAuthHeader(){
        $epoch = time();
        if ($this->debug){ $this->debugger("Timestamp: $epoch"); }




        $authString = "yourString";

        return $authString;
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