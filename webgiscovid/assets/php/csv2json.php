<?php
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Jakarta');
function csvToJson($fname) {
    // open csv file
    if (!($fp = fopen($fname, 'r'))) {
        die("Can't open file...");
    }
    //read csv headers
    $key = fgetcsv($fp,"1024","|");  
    // parse csv rows into array
    $json = array();
    while ($row = fgetcsv($fp,"1024","|")) {
        $json[] = array_combine($key, $row);
    }    
    // release file handle
    fclose($fp);
    $data = array('features'=>$json);
    // encode array to json
    return json_encode($data);
}
//$covid19 = csvToJson("assets/covid19/covidkab-6.csv");
$covid19 = csvToJson("http://siaga.bnpb.go.id/pm/covidkab.csv");
$fp = fopen('assets/covid19/covidkab-6.json', 'w');
fwrite($fp,$covid19);
fclose($fp);
