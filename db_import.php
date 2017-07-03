<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 30/06/2017
 * Time: 23:23
 */


$user = 'root';
$password = 'root';
$db_name = 'type_ahead';
$host = 'localhost';
$port = 8889;



$db = new mysqli($host, $user, $password, $db_name);


if($db->connect_errno > 0){
    die('Unable to connect to database [' . $db->connect_error . ']');
}

$sql = <<<SQL
  SELECT * 
  FROM `Geography` 
  WHERE 1
SQL;

if(!$result = $db->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
}

echo 'Total results: ' . $result->num_rows;