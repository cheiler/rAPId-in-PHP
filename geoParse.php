<?php
/**
 * Created by PhpStorm.
 * User: christianheiler
 * Date: 02/07/2017
 * Time: 18:28
 */

ini_set('memory_limit','100000M');

$string = file_get_contents("Geo.json");
$json_a = json_decode($string, true);

$data = array();

echo "<pre>";
# print_r($json_a);


foreach($json_a as $set){
    foreach($set as $item){
        if (isset($item['id'])){
            $data[$item['id']]['type'] = $item['type'];
            if(isset($item['country_code'])) { $data[$item['id']]['country_code'] = $item['country_code']; }
            if(isset($item['name_full'])) { $data[$item['id']]['name_full'] = $item['name_full']; }
            if(isset($item['property_ids_expanded'])) { $data[$item['id']]['prop_ext'] = count($item['property_ids_expanded']); }
            if(isset($item['property_ids'])) { $data[$item['id']]['prop'] = count($item['property_ids']); }
        }
    }

}


foreach($data as $id => $item){
    echo "$id";
    echo "\t";
    if(isset($item['name_full'])){ echo $item['name_full']; }
    echo "\t";
    if(isset($item['type'])){ echo $item['type']; }
    echo "\t";
    if(isset($item['country_code'])){ echo $item['country_code']; }
    echo "\t";
    if(isset($item['prop'])){ echo $item['prop']; }
    echo "\t";
    if(isset($item['prop_ext'])){ echo $item['prop_ext']; }
    echo "\n";
}



echo "</pre>";




