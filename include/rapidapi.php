<?php

/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 04/06/2017
 * Time: 18:52
 */
class rapidapi
{
    private $apiKey="";
    private $sharedSecret="";
    private $baseUrl= "https://api.ean.com";
    private $bookingUrl = " https://book.api.ean.com";
    private $version = 1;
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
     * @return array
     */
    public function shop(array $hotelIdArray)
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




        return (array)"This was shop";
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
     * @return rapidapi
     */
    public function setArrival($arrival)
    {
        $this->arrival = $arrival;
        return $this;
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
     * @return rapidapi
     */
    public function setDeparture($departure)
    {
        $this->departure = $departure;
        return $this;
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
     * @return rapidapi
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
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
     * @return rapidapi
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
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
     * @return rapidapi
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
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

    



}