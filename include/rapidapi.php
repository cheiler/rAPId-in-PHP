<?php

/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 15/01/2019
 * Time: 18:52
 */
class rapidapi
{
    private $apiKey="";
    private $sharedSecret="";
    private $baseUrl= "https://test.ean.com";
    private $bookingUrl = " https://book.api.ean.com";
    private $version = "2.2";
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
    private $downloadPath = "./files/";
    private $debug;
    private $include_plain_text = false;
    private $follow_link = "";
    private $request;
    private $response;
    private $allowed_methods = array("GET", "POST", "PUT", "DELETE");


    function __construct(){
        $this->debug = false;
        $this->downloadPath = trim($this->downloadPath);
        $this->request = new stdClass();
        $this->response = new stdClass();
        $this->response->raw = "";
        $this->response->header_plain = "";
        $this->response->header = new stdClass();
        $this->response->body_plain = "";
        $this->response->body = new stdClass();
        $this->request->method = "GET";
        $this->request->path = "";
        $this->request->body = "";
        
    }

    /**
     * @param $msg
     * @internal
     */
    private function debugger($msg)
    {
        if ($this->debug){
            if($this->debug){
                if(is_object($msg)){
                    echo "\n";
                    print_r($msg);
                    echo "\n";
                } else {
                    echo "\n$msg\n";
                }

            }

        }

    }

    /**
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
     * @private
     * @param string $method
     * @param string $query
     * @param string $payload
     * @return object
     */
    public function apiWrapper($method, $query, $payload="", $payload_type=null){
        $header[] = "Accept: application/json";
        $header[] = "Authorization: ".$this->getAuthHeader();
        $header[] = "X-Forward-For: ".$this->xForward;
        $header[] = "Customer-Ip: ".$this->customerIP;
        $header[] = "User-Agent: ".$this->userAgent;
        if($payload_type != null){
            $header[] = "Content-Type: ".$payload_type;
        }



        if(!in_array($method, $this->allowed_methods)){
            $this->debugger("Method $method not in allowed methods. only allowed: GET, POST, PUT, DELETE");
            return null;
        }

        $url = $this->getBaseUrl()."/".$this->version."/".$query;

        $this->debugger("Headers: ".print_r($header, true));
        $this->debugger("Method: $method");
        $this->debugger("URL: ".$url);
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        //curl_setopt($ch,CURLOPT_POST,5);

        if($payload != ""){
            curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
        }

        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch,CURLOPT_ENCODING , "gzip");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        $this->debugger("curling");
        $response = curl_exec($ch);

        $info = curl_getinfo($ch);
        $this->debugger("HTTP Status: ".print_r($info, true));

        //$response = json_decode($response);

        $this->debugger($response);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $back = new stdClass();

        if($this->include_plain_text){
            $back->header_text = $header;
            $back->body_text = $body;
        }

        $header_array_response = $this->make_header_array($header);
        $back->header = $header_array_response->header;
        $back->body = json_decode($body);
        $back->status = $header_array_response->status;
        $back->status_message = $header_array_response->status_message;

        //set internal variables
        $this->response->header_plain = $header;
        $this->response->header = $back->header;
        $this->response->body_plain = $body;
        $this->response->body = $back->body;
        $this->response->status = $back->status;
        $this->response->status_message = $back->status_message;

        return $back;
    }




    public function send_request(){
        $allowed_methods = array("GET", "POST", "PUT", "DELETE");
        if(!in_array($this->request->method, $allowed_methods)){
            $this->debugger("Method ".$this->request->method." not in allowed methods. only allowed: GET, POST, PUT, DELETE");
            $this->setError(503, "Method now allowed: $this->request->method");
            return null;
        }
        if($this->request->path==""){
            $this->debugger("Request Path empty");
            $this->setError(503, "Request path can't be empty");
            return null;
        }
        $this->debugger("Requesting: ".$this->request->method." Path: ".$this->request->path);
        $response = $this->apiWrapper($this->request->method, $this->request->path, $this->request->body);
        return $response;
    }


    /**
     * @param $error_code
     * @param $error_message
     * @return bool
     */
    private function setError($error_code, $error_message){
        $this->debugger("Set Error: $error_code - $error_message");
        $this->response->header = "";
        $this->response->header_plain = "";
        $this->response->body = new stdClass();
        $this->response->body_plain = "";
        $this->response->status = $error_code;
        $this->response->status_message = $error_message;
        return true;
    }




    /**
     * @public
     * @param $hotelIdArray array
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
     * @param string $property_id
     * @return object
     */
    public function tripadvisor($property_id){
        $method="GET";
        $path="properties/tripadvisor?property_id=$property_id";
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
     * @param array $property_ids
     * @return bool|object
     */
    public function tripadvisor_multi($property_ids){
        if(!is_array($property_ids)){
            echo "ERROR input must be an array!\n";
            return false;
        }
        $prop_list = "";
        foreach($property_ids as $property_id){
            $prop_list .= "property_id=$property_id&";
        }
        $method="GET";
        $path="properties/tripadvisor?$prop_list";
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
     *
     */
    public function polygon($json){
        //Basic validation
        if(!is_object($json)){
            $this->setError(415, "Input is not an object");
            return false;
        }
        if(!isset($json->type) && $json->type = "Polygon"){
            $this->setError(415, "Type is not set as `Polygon`, with correct casing");
            return false;
        }
        if(!isset($json->coordinates) && is_array($json->coordiantes)){
            $this->setError(415, "`coordinates` not set or not an array");
            return false;
        }
        if(count($json->coordinates) != 1){
            $this->setError(415, "Rapid API only supports single Polygons, you have send ".count($json->coordiantes));
            return false;
        }
        //check if closed
        $length = count($json->coordinates[0]);
        if($length < 4){
            $this->setError(415, "A correctly formed polygon has at least 4 coordinates, with the last one the same as the first to cloe the polygon, you supplied only $length coordinates");
            return false;
        }
        if($json->coordiantes[0][0] != $json->coordiantes[0][$length-1]){
            $this->setError(415, "First and last polygon element must be identical");
            return false;
        }
        $this->debugger("Polygon - All tests passed, going to call Rapid");

        return $this->apiWrapper("POST", "properties/geography?include=property_ids", json_encode($json), "application/json");

    }


    /**
     * @param array $propertyIds
     * @param string $locale
     * @return boolean
     */
    public function property(array $propertyIds, $locale = "en-US"){
        if(!is_array($propertyIds)){
            $this->setError(503, "propertyIds must be an array");
            $this->debugger("property Ids not set as an array in function property");
            return false;
        } elseif (count($propertyIds) >= 251){
            $this->setError(503, "propertyIds array contains more than 250 entries");
            $this->debugger("property Ids array contains more than 250 entries.");
            return false;
        }

        $method="GET";
        $path="files/properties/catalog?language=$locale";
        $property_id_string = "";
        foreach ($propertyIds as $property_id){
            $property_id_string .= "&property_id=".$property_id;
        }
        $return_path = $path.$property_id_string;

        $this->setRequestMethod($method);
        $this->setRequestPath($return_path);
        $this->setRequestBody("");

        return true;

    }

    /**
     * @param $region_id
     * @param string $language
     * @param bool $details
     * @param bool $properties
     * @param bool $properties_expanded
     * @return bool
     */
    public function regionId($region_id, $language = "end-US", $details = true, $properties = true, $properties_expanded = true){
        //At least one include must be true;
        if(!$details && !$properties && !$properties_expanded){
            $this->setError(400, "At least one of the include parameters must be true");
            return false;
        }

        $this->setRequestMethod("GET");

        $path = "regions/$region_id?language=$language";
        $path .= ($details ? "&include=details" : "");
        $path .= ($properties ? "&include=property_ids" : "");
        $path .= ($properties_expanded ? "&include=property_ids_expanded" : "");

        $this->setRequestPath($path);

        return true;

    }

    /**
     * @public
     * @param string $language
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
     * @public
     * @param string $language
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
    
    public function initGeoPagination($startRegion = 0, $details = true, $properties = true, $properties_expanded=true){
        $path = "regions?language=".$this->language;
        if($details){
            $path .= "&include=details";
        }
        if($properties){
            $path .= "&include=property_ids";
        }
        if($properties_expanded){
            $path .= "&include=property_ids_expanded";
        }

        if($startRegion != 0){
            $path .= "&ancestor_id=".$startRegion;
        }
        $this->request->path = $path;

        return true;
    }
    
    

    /**
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
        $old = 0;
        while(!feof($i_handle)) {

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
     * @param $header_plain_text
     * @return stdClass
     */
    private function make_header_array($header_plain_text){
        $header_array = explode("\n", $header_plain_text);
        $head_clean = array();

        $back = new stdClass();
        $back->header = new stdClass();

        foreach($header_array as $line){
            if(substr($line,0,5) == "HTTP/"){
                $value = explode(" ", $line);
                $back->status = $value[1];
                $back->status_message = $value[2];
            } else {
                $value = explode(":", $line, 2);
                if($value[0] != ""){
                    $head_clean[$value[0]] = trim($value[1]);
                    $back->header->{$value[0]} = trim($value[1]);
                    if($value[0] == "Link"){
                        $link_start = strpos($value[1], "<")+1;//adding 1 to exclude "<" sign
                        $link_length = strpos($value[1], ">")-$link_start;//
                        $back->Link = new stdClass();
                        $back->Link->href = substr($value[1], $link_start, $link_length);
                        $this->follow_link = $back->Link->href;
                    }
                }
            }

        }

        return $back;
    }

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

    public function setDebug($value){
        $this->debug = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludePlainText()
    {
        return $this->include_plain_text;
    }

    /**
     * @param bool $include_plain_text
     */
    public function setIncludePlainText($include_plain_text)
    {
        $this->include_plain_text = $include_plain_text;
    }
    /**
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->request->method;
    }

    /**
     * @param string $requestMethod
     * @return boolean
     */
    public function setRequestMethod($requestMethod)
    {
        if(in_array($requestMethod, $this->allowed_methods)){
            $this->request->method = $requestMethod;
            return true;
        }
        return false;

    }

    /**
     * @return string
     */
    public function getRequestPath()
    {
        return $this->request->path;
    }

    /**
     * @param string $requestPath
     * @return rapidapi
     */
    public function setRequestPath($requestPath)
    {
        $this->request->path = $requestPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestBody()
    {
        return $this->request->body;
    }

    /**
     * @param string $requestBody
     * @return rapidapi
     */
    public function setRequestBody($requestBody)
    {
        $this->request->body = $requestBody;
        return $this;
    }

    //GETTERS for responses.

    /**
     * @return string
     */
    public function getResponseBody()
    {
        return $this->response->body;
    }
    //short
    /**
     * @return string
     */
    public function body()
    {
        return $this->response->body;
    }

    /**
     * @return string
     */
    public function getResponseHeader()
    {
        return $this->response->header;
    }
    /**
     * @return string
     */
    public function head()
    {
        return $this->response->header;
    }





}
