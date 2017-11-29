<?php

require_once('includes/shopify.php');

$parameters['limit'] = 250;
$parameters['page'] = 1;
    

$t1 = microtime(true);

$request = 'https://af4553f71a3f4a1c2d76d3d3fd3866f4:fcf13188c314da4bb8d7f6731bf29218@vcshopify2.myshopify.com/admin/products.json?limit=2&page=';


$chanel = [];
// create both cURL resources
for ($i = 1; $i <= 3; $i++) {
    $ch = curl_init();
    $rq = $request + $i;
    curl_setopt($ch, CURLOPT_URL, $rq);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    $chanel[] = $ch;
}

//create the multiple cURL handle
$mh = curl_multi_init();

//add the two handles
foreach ($chanel as $ch) {
    curl_multi_add_handle($mh,$ch);
}

$active = null;
//execute the handles
do {
    $mrc = curl_multi_exec($mh, $active);
} while ($mrc == CURLM_CALL_MULTI_PERFORM);

while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($mh) != -1) {
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
}

foreach ($chanel as $key => $ch) {
    $r = curl_multi_getcontent($ch);
    var_dump($r);
    curl_multi_remove_handle($mh,$ch);
}

curl_multi_close($mh);

$t2 = microtime(true);
$time = $t2 - $t1;

echo "GET products in " . $time . '<br/>';

echo '<pre>';
//    print_r($resp);
echo '</pre>';
exit;

?>