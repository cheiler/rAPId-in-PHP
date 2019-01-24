<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 04/06/2017
 * Time: 18:53
 */

echo "start";

require_once ("include/rapidapi.php");
include_once ("./config/config.php");


$rapid = new rapidapi();
$rapid->setApiKey($cfg['apikey']);
$rapid->setSharedSecret($cfg['secret']);
$rapid->setDebug(false);
$rapid->setIncludePlainText(false);



//$data = $rapid->geoCatalog();
//$data = $rapid->geoCatalog();

#echo "Hello: $data";

$api = $rapid->setArrival("2017-y-20");

//$api = $rapid->apiWrapper("GET",'properties/content?language=en-US&updated=2019-01-21');

$rapid->setRequestMethod("GET");

$rapid->setRequestPath("properties/content?language=en-US&updated=2019-01-21");

$api = $rapid->send_request();



//print_r($api);

//print_r($api->header);
//print_r($api->header_text);

print_r($rapid->head());
