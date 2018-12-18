<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 04/06/2017
 * Time: 18:53
 */

require_once ("include/rapidapi.php");


$rapid = new rapidapi();
$rapid->setApiKey("apiKey");
$rapid->setSharedSecret("mySecret");



$data = $rapid->geoCatalog();


echo "Hello: $data";