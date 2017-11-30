<?php

require_once('includes/shopify.php');

$parameters['limit'] = 250;
$parameters['page'] = 1;
    

$t1 = microtime(true);

$request = 'https://af4553f71a3f4a1c2d76d3d3fd3866f4:fcf13188c314da4bb8d7f6731bf29218@vcshopify2.myshopify.com/admin/products.json?limit=100&page=';


// create both cURL resources
for ($i = 41; $i <= 60; $i++) {
    $curl = curl_init();
    $link = $request . $i;
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 1);
    curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 10); 
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

    $data = curl_exec($curl);   

    echo $data;
    
    curl_close($curl);
}

$t2 = microtime(true);
$time = $t2 - $t1;

echo "GET products in " . $time . '<br/>';

echo '<pre>';
//    print_r($resp);
echo '</pre>';
exit;

?>