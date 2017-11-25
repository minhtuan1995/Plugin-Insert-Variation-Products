<?php

$test = 'https://cdn.shopify.com/s/files/1/2511/8610/products/81wiyxqYopL._UL1500.jpg?v=1509681769';

$test = parse_url($test);

echo '<pre>';
    print_r($test);
echo '</pre>';
exit;
        
?>