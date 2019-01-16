<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 04/06/2017
 * Time: 18:53
 */

echo "start";

require_once ("include/rapidapi.php");


$rapid = new rapidapi();
$rapid->setApiKey("myAPIKEy");
$rapid->setSharedSecret("YourSharedSecret");



$data = $rapid->geoCatalog();

#echo "Hello: $data";

$api = $rapid->setArrival("2017-y-20");

# $api = $rapid->apiWrapper("GET",'regions/2114?language=en-US&include=DETAILS&include=PROPERTY_IDS&include=PROPERTY_IDS_EXPANDED');

print_r($api);


