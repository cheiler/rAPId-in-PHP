<?php
/**
 * Created by PhpStorm.
 * User: cheiler
 * Date: 2019-03-28
 * Time: 13:41
 */

class polygon
{
    private $outer_array = array();
    private $inner_array = array();
    private $debug;
    private $report = array();

    public function __construct()
    {
        $this->debug = false;
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
                    $this->report[] = print_r($msg,true);

                } else {
                    echo "\n$msg\n";
                    $this->report[] = $msg;
                }
            }
        }
    }

    /**
     * @param string $name
     * @return bool|string returns the name of the polygon if created;
     */
    public function create($name = "1"){
        if(isset($this->inner_array[$name])){
            return false;
        }
        $this->inner_array[$name] = array();
        return $name;
    }

    /**
     * @param $polygon_name
     * @param $lat
     * @param $long
     * @return bool
     */
    public function add_point($polygon_name, $lat, $long){
        if(!isset($this->inner_array[$polygon_name])){
            $this->debugger("Polygon $polygon_name does not exist, pleae create the polygon first.");
            return false;
        }
        $this->inner_array[$polygon_name][] = array("long" => $long, "lat"=>$lat);
        return true;
    }



    public function close($polygon_name){
        if(!isset($this->inner_array[$polygon_name])){
            $this->debugger("Polygon $polygon_name does not exist, pleae create the polygon first.");
            return false;
        }
        if(count($this->inner_array[$polygon_name])<3){
            $this->debugger("Polygon $polygon_name has less than 3 elements, can't close");
            return false;
        }
        $this->inner_array[$polygon_name][] = $this->inner_array[$polygon_name][0];
        return true;
    }


    public function get_geoJson(){
        $this->outer_array = array();
        foreach($this->inner_array as $inner){
            $new = array();
            foreach($inner as $in){
                $new[] = array($in["long"], $in["lat"]);
            }

            $this->outer_array[] = $new;
        }

        $json = new stdClass();
        $json->type = "Polygon";
        $json->coordinates = $this->outer_array;

        return $json;
    }

    public function get_geoString(){
        $json = $this->get_geoJson();
        return json_encode($json,JSON_PRETTY_PRINT);
    }


}