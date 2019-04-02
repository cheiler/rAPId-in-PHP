<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 2019-03-28
 * Time: 19:57
 */

require_once ("./include/polygon.php");
require_once ("./include/rapidapi.php");
include_once ("./config/config.php");

$polygon = new polygon();

$pname = $polygon->create("main");

print_r($pname);

$polygon->add_point($pname, 37.169329, -93.446782);
$polygon->add_point($pname, 37.169432, -93.4244);
$polygon->add_point($pname, 37.168636, -93.371097);

$polygon->add_point($pname, 37.139236, -93.376295);
$polygon->add_point($pname, 37.138634, -93.361419);
$polygon->add_point($pname, 37.100601, -93.347109);

$polygon->add_point($pname, 37.095905, -93.215792);
$polygon->add_point($pname, 37.138013, -93.215259);
$polygon->add_point($pname, 37.141503, -93.189332);

$polygon->add_point($pname, 37.333322, -93.191278);
$polygon->add_point($pname, 37.339372, -93.438268);


$result = $polygon->close($pname);

//print_r($result);

echo "\n";

//$json = $polygon->get_geoJson();

//print_r($json);

echo "\n";

$output = $polygon->get_geoString();
echo $output;

/**
echo "\n----RAPID START---\n";

$rapid = new rapidapi();

$rapid = new rapidapi();
$rapid->setApiKey($cfg['apikey']);
$rapid->setSharedSecret($cfg['secret']);
$rapid->setDebug(false);
$rapid->setIncludePlainText(false);



$rapid->setDebug(true);

$result = $rapid->polygon($json);


print_r($result);

echo "\n----RAPID END---\n";

**/

($polygon->get_bounding_rectangle("main"));

print_r($polygon->bounding->get_geoJson());