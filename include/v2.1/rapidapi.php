<?php

/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 7/1/2019
 * Time: 0:52
 * Comment:
 * Retiring 2.1
 */
class rapidapi
{
    private $apiKey="";
    private $sharedSecret="";
    private $baseUrl= "https://api.ean.com";
    private $bookingUrl = " https://book.api.ean.com";
    private $version = "2.1";
    private $customerIP = "128.0.0.1";
    private $xForward = "128.0.0.2";
    private $arrival;
    private $departure;
    private $currency = "USD";
    private $language = "en-US";
    private $country = "US";
    private $occupancy = 1;
    private $sales_channel = "Website";
    private $sales_environment = "HOTEL_ONLY";
    private $sortType = "PREFERRED";
    private $userAgent = "Don.t tellU 5.3";
    private $downloadPath = "../files/";

    function __construct(){
        $this->debug = true;
        $this->downloadPath = trim($this->downloadPath);
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
                #$msg = str_replace('"', '\\"', $msg);
                #$msg = htmlentities($msg);
                echo "\n<script>console.log('$msg')</script>\n";

            }

        }

    }

    /**
     * @name getAuthHeader
     * @public
     * @return string
     */
    public function getAuthHeader(){
        $epoch = time();
        #$epoch = 1497349365;
        $this->debugger("Timestamp: $epoch");
        $toEncode = $this->apiKey . $this->sharedSecret . $epoch;
        $this->debugger("String to Encode: $toEncode");
        $hash = hash('sha512',"$toEncode");
        $this->debugger("Hash: $hash");
        $auth_header_string = "EAN apikey=". $this->apiKey . ",signature=" . $hash . ",timestamp=" . $epoch;
        $this->debugger("Authentication Header: $auth_header_string");
        return $auth_header_string;
    }

    /**
     * @name apiWrapper
     * @private
     * @param string $method
     * @param string $query
     * @return object
     */
    public function apiWrapper($method, $query){
        $header[] = "Accept: application/json";
        $header[] = "Authorization: ".$this->getAuthHeader();
        $header[] = "X-Forward-For: ".$this->xForward;
        $header[] = "Customer-Ip: ".$this->customerIP;
        $header[] = "User-Agent: ".$this->userAgent;

        #TODO: Query Validation?

        $url = $this->getBaseUrl()."/".$this->version."/".$query;

        $this->debugger("Headers: ".print_r($header, true));
        $this->debugger("Method: $method");
        $this->debugger("URL: ".$url);
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch,CURLOPT_POST,5);
        //curl_setopt($ch,CURLOPT_POSTFIELDS,$XML);
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch,CURLOPT_ENCODING , "gzip");
        echo "<br>curling<br>";
        $response = curl_exec($ch);

        $info = curl_getinfo($ch);
        $this->debugger("HTTP Status: ".print_r($info, true));

        print_r($response);
        $response = json_decode($response);

        print_r($response);
        return $response;
    }



    /**
     * @name shop
     * @public
     * @param $hotelIdArray array
     * @param $checkinDate string
     * @param $checkoutDate string
     * @return array
     */
    public function shop( $hotelIdArray )
    {
        $back = array("code" => 500, "msg" => "HotelIdArray must be an array");
        if (!is_array($hotelIdArray)) {
            return $back;
        }
        $hotelIDparam = "";
        foreach ($hotelIdArray as $hid) {
            $hotelIDparam .= "&property_id=".$hid;
        }
        $url = $this->getBaseUrl()."/".$this->version."/";


        $call["url"] = $url;

        return $call;
    }


    /**
     * @name geoCatalog
     * @public
     * @return object
     */
    public function geoCatalog()
    {
        $method="GET";
        $path="files/properties/catalog";
        if($response = $this->apiWrapper($method,$path)){
            $this->debugger("Response retrieved");
            $this->debugger($response);
        } else {
            $this->debugger("Response Error: Properties Catalog");
            exit(503|"Response Error");
        }

        return $response;


    }

    /**
     * @param array $propertyId
     * @param string $locale
     */
    public function property(array $propertyId, $locale = "en-US"){
        $method="GET";
        $path="files/properties/catalog?language=$language";
    }




    /**
     * @name contentCatalog
     * @public
     * @return object
     */
    public function contentCatalog($language = "en-US")
    {
        $method="GET";
        $path="files/properties/catalog?language=$language";
        if($response = $this->apiWrapper($method,$path)){
            $this->debugger("Response retrieved");
            $this->debugger(print_r($response, true));
        } else {
            $this->debugger("Response Error: Properties Catalog");
            exit(503|"Response Error");
        }

        return $response;

    }


    /**
     * @name contentComplete
     * @public
     * @return object
     */
    public function contentComplete($language = "en-US")
    {
        //TODO: language entry validation
        $method="GET";
        $path="files/properties/content?language=$language";
        if($response = $this->apiWrapper($method,$path)){
            $this->debugger("Response retrieved");
            $this->debugger(print_r($response, true));
        } else {
            $this->debugger("Response Error: Properties Catalog");
            exit(503|"Response Error");
        }

        return $response;

    }

   /**
     * @name download
     * @public
     * @param $url
     * @param $file
     * @param $output Boolean Is output true of false
     * @param $outputSize Integer output every X bytes
     * @return boolean
     */
    public function download($url, $file, $output = false, $outputSize = 17000000)
    {
        //validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->debugger("Incorrect URL: ".$url);
            return false;
        }
        //create download directory
        if (!file_exists($this->downloadPath)) {
            mkdir($this->downloadPath, 0777, true);
        }
        $path=$this->downloadPath.$file;


        /* if (file_put_contents($path, file_get_contents($url))){
            return true;
        }
        */

        if($this->copyfile_chunked($url, $path, $output, $outputSize)){
            return true;
        }


        return false;
    }


    /**
     * @name: copyfile_chunked
     * Copy remote file over HTTP one small chunk at a time.
     *
     * @param $infile String The full URL to the remote file
     * @param $outfile String The path where to save the file
     * @param $output Boolean Is output true of false
     * @param $outputSize Integer output every X bytes
     * @return boolean
     */
    private function copyfile_chunked($infile, $outfile, $output = false, $outputSize = 17000000) {
        $chunksize = 10 * (1024 * 1024); // 10 Megs

        /**
         * parse_url breaks a part a URL into it's parts, i.e. host, path,
         * query string, etc.
         */
        $parts = parse_url($infile);
        $i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
        $o_handle = fopen($outfile, 'wb');

        if ($i_handle == false || $o_handle == false) {
            return false;
        }

        if (!empty($parts['query'])) {
            $parts['path'] .= '?' . $parts['query'];
        }

        /**
         * Send the request to the server for the file
         */
        $request = "GET {$parts['path']} HTTP/1.1\r\n";
        $request .= "Host: {$parts['host']}\r\n";
        $request .= "User-Agent: Mozilla/5.0\r\n";
        $request .= "Keep-Alive: 115\r\n";
        $request .= "Connection: keep-alive\r\n\r\n";
        fwrite($i_handle, $request);

        /**
         * Now read the headers from the remote server. We'll need
         * to get the content length.
         */
        $headers = array();
        
        while(!feof($i_handle)) {
            $line = fgets($i_handle);
            if ($line == "\r\n") break;
            $headers[] = $line;
        }

        /**
         * Look for the Content-Length header, and get the size
         * of the remote file.
         */
        $length = 0;
        foreach($headers as $header) {
            if (stripos($header, 'Content-Length:') === 0) {
                $length = (int)str_replace('Content-Length: ', '', $header);
                break;
            }
        }

        /**
         * Start reading in the remote file, and writing it to the
         * local file one chunk at a time.
         */
        $cnt = 0;
        $counter = 0;
        $old = 0;
        while(!feof($i_handle)) {
            $buf = '';
            $buf = fread($i_handle, $chunksize);
            $bytes = fwrite($o_handle, $buf);
            if ($bytes == false) {
                return false;
            }
            $cnt += $bytes;
            //TODO: work on display only for debug;
            if ($output){

                $divisor = $outputSize; //for update speed...
                if(round($cnt/$divisor) !== $old){
                    //echo round($cnt/1000000,2)."MB of ".round($length/1000000)."MB\r";
                    $this->terminal_statusbar($cnt,$length,50);
                    $old = round($cnt/$divisor);
                }
            }

            	
            
            /**
             * We're done reading when we've reached the conent length
             */
            if ($cnt >= $length) break;
        }

        fclose($i_handle);
        fclose($o_handle);
        return $cnt;
    }//END copyfile_chunked


    /**
     * @param $current
     * @param $max
     * @param $length
     */
    private function terminal_statusbar($current, $max, $length){
        //calculate how many percentage points 1 unit is
        $filledBlocks = round($current*$length/$max);
        $emptyBlocks = $length - $filledBlocks;

        echo "Downloading: |";

        for ($x = 0; $x <= $filledBlocks; $x++) {
            echo "█";
        }
        for ($x = 0; $x <= $emptyBlocks; $x++) {
            echo " ";
        }
        echo "| ";
        echo round($current/1000000,2)."MB of ".round($max/1000000)."MB       \r";

    }



    /**
     * @name uncompress
     * @public
     * @param $source
     * @param $target
     * @return bool
     */
    public function uncompress($source, $target){

        $srcFile = $this->downloadPath.trim($source);
        $toFile = $this->downloadPath.trim($target);

        if(!file_exists($srcFile)){
            $this->debugger("Source file does not exist");
            return false;
        }

        $sfp = gzopen($srcFile, "rb");
        $fp = fopen($toFile, "w");

        while ($string = gzread($sfp, 4096)) {
            fwrite($fp, $string, strlen($string));
        }
        gzclose($sfp);
        fclose($fp);
        return true;
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

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     * @return rapidapi
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getBookingUrl()
    {
        return $this->bookingUrl;
    }

    /**
     * @param string $bookingUrl
     * @return rapidapi
     */
    public function setBookingUrl($bookingUrl)
    {
        $this->bookingUrl = $bookingUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return rapidapi
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerIP()
    {
        return $this->customerIP;
    }

    /**
     * @param string $customerIP
     * @return rapidapi
     */
    public function setCustomerIP($customerIP)
    {
        $this->customerIP = $customerIP;
        return $this;
    }

    /**
     * @return string
     */
    public function getXForward()
    {
        return $this->xForward;
    }

    /**
     * @param string $xForward
     * @return rapidapi
     */
    public function setXForward($xForward)
    {
        $this->xForward = $xForward;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArrival()
    {
        return $this->arrival;
    }

    /**
     * @param mixed $arrival
     * @return boolean
     */
    public function setArrival($arrival)
    {
        # ^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$
        $isDate = preg_match("/^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$/", $arrival);
        if($isDate){
            $this->arrival = $arrival;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return mixed
     */
    public function getDeparture()
    {
        return $this->departure;
    }

    /**
     * @param mixed $departure
     * @return boolean
     */
    public function setDeparture($departure)
    {
        # ^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$
        $isDate = preg_match("/^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$/", $departure);
        if($isDate){
            $this->departure = $departure;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return boolean
     */
    public function setCurrency($currency)
    {
        # ^[A-Z]{3}$
        $check = preg_match("/^[A-Z]{3}$/", $currency);
        if($check){
            $this->currency = $currency;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return boolean
     */
    public function setLanguage($language)
    {
        # ^[a-z]{2}-[A-Z]{2}$
        $check = preg_match("/^[a-z]{2}-[A-Z]{2}$/", $language);
        if($check){
            $this->language = $language;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return boolean
     */
    public function setCountry($country)
    {
        # ^[A-Z]{2}$
        $check = preg_match("/^[A-Z]{2}$/", $country);
        if($check){
            $this->country = $country;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return int
     */
    public function getOccupancy()
    {
        return $this->occupancy;
    }

    /**
     * @param int $occupancy
     * @return rapidapi
     */
    public function setOccupancy($occupancy)
    {
        $this->occupancy = $occupancy;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalesChannel()
    {
        return $this->sales_channel;
    }

    /**
     * @param string $sales_channel
     * @return rapidapi
     */
    public function setSalesChannel($sales_channel)
    {
        $this->sales_channel = $sales_channel;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalesEnvironment()
    {
        return $this->sales_environment;
    }

    /**
     * @param string $sales_environment
     * @return rapidapi
     */
    public function setSalesEnvironment($sales_environment)
    {
        $this->sales_environment = $sales_environment;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * @param string $sortType
     * @return rapidapi
     */
    public function setSortType($sortType)
    {
        $this->sortType = $sortType;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     * @return rapidapi
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    



}
