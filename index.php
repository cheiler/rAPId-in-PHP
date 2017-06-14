<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 04/06/2017
 * Time: 18:53
 */

require_once ("include/rapidapi.php");


$rapid = new rapidapi();
$rapid->setApiKey("5ji84kjgss19qcerldo0lhp93g");
$rapid->setSharedSecret("csa6srhcg9ocb");



$data = $rapid->getAuthHeader();

echo "Hello: $data";


$api = $rapid->apiWrapper("GET",'regions/2114?language=en-US&include=DETAILS&include=PROPERTY_IDS&include=PROPERTY_IDS_EXPANDED');


print_r($api);

