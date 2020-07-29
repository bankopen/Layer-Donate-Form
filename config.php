<?php
//Set parameters 
$sandbox = 'no';
$access_key = '02df0140-d027-11ea-abd0-5f4514af1aca';
$secret_key = 'ae065867c73293b2fca66cf55c9855eebd912047';
$currency = 'INR';
$redirect_page = 'response.php';

//path identifer code for flexible use to any server location
//If integrated in other platform, this portion can be modified as required
$scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ('http'. (($_SERVER['SERVER_PORT'] == '443') ? 's' : ''));
$port = ($_SERVER['SERVER_PORT'] != '80' && $scheme != 'https')? (':'. $_SERVER['SERVER_PORT']) : '';
$path = substr($_SERVER['REQUEST_URI'],0,strrpos($_SERVER['REQUEST_URI'],'/'));
$redirect_page = sprintf('%s://%s%s%s', $scheme,$_SERVER['SERVER_NAME'], $port, $path.'/'.$redirect_page);
$local_path = sprintf('%s://%s%s%s', $scheme,$_SERVER['SERVER_NAME'], $port, $path);
require_once 'layer_api.php';

//Hash functions requried in both request and response
function create_hash($data,$access_key,$secret_key){
    ksort($data);
    $hash_string = $access_key;
    foreach ($data as $key=>$value){
        $hash_string .= '|'.$value;
    }
    return hash_hmac("sha256",$hash_string,$secret_key);
}

function verify_hash($data,$rec_hash,$access_key,$secret_key){
    $gen_hash = create_hash($data,$access_key,$secret_key);
    if($gen_hash === $rec_hash){
        return true;
    }
    return false;
}