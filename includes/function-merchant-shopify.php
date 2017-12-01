<?php

require_once( WC_PLUGIN_DIR . '/google-api/vendor/autoload.php' );
//require_once('../google-api/vendor/autoload.php' );

if (!defined('MAX_PRODUCT_BATCH')) {
    define('MAX_PRODUCT_BATCH', 250); 
}

function shopify_feed_main_page() {
    
    if (ob_get_level() == 0) {
        ob_start();
    }
    prefix_enqueue();
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    echo '<div class="wrap">';
                            
    if (isset($_POST['process_merchantFeed']) || isset($_GET['code'])) {

        if (isset($_POST['client_id'])) {
            // Shopify Dev
            $_SESSION['shop_url'] = $_POST['shop_url'];
            $_SESSION['shop_api_key'] = $_POST['shop_api_key'];
            $_SESSION['shop_secret_key'] = $_POST['shop_secret_key'];
            // Google Dev
            $_SESSION['client_id'] = $_POST['client_id'];
            $_SESSION['client_secret'] = $_POST['client_secret'];
            $_SESSION['redirect_url'] = $_POST['redirect_url'];
            // Merchant Information
            $_SESSION['merchant_id'] = $_POST['merchant_id'];
            $_SESSION['shop_domain'] = $_POST['shop_domain'];
            // Shipping Information
            $_SESSION['shipping_price'] = $_POST['shipping_price'];
            $_SESSION['shipping_service'] = $_POST['shipping_service'];
            
            $_SESSION['start_product'] = $_POST['start_product'];
            $_SESSION['end_product'] = $_POST['end_product'];
            
        }

        $client = new Google_Client();
        //$client->setAuthConfig('client-secrets02.json');  
        $client->setClientId($_SESSION['client_id']);
        $client->setClientSecret($_SESSION['client_secret']);
        $client->setRedirectUri($_SESSION['redirect_url']);      // redirect uri
        $client->setScopes(Google_Service_ShoppingContent::CONTENT);

        if (isset($_GET['logout'])) { // logout: destroy token
            unset($_SESSION['token']);
            die('Logged out.');
        }

        if (isset($_GET['code'])) { // we received the positive auth callback, get the token and store it in session
            $client->authenticate($_GET['code']);
            $_SESSION['token'] = $client->getAccessToken();
        }

        if (isset($_SESSION['token'])) { // extract token from session and configure client
            $token = $_SESSION['token'];
            $client->setAccessToken($token);
        }

        if (!$client->getAccessToken()) { // auth call to google
            $authUrl = $client->createAuthUrl();
            header("Location: " . $authUrl);
            die;
        }
        
        create_iframe('iframe-shopify-feed-merchant', 'Shopify Feed', 'Requesting...');
        
    } else {
        $current_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        echo '<div class="row">
                <div class="col-lg-9">
                    <div class="panel panel-default"> 
                       <div class="panel-heading">Shopify Feed</div>
                        <div class="panel-body">
                            <div class="row">';
        echo '<form role="form" method="post">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>Shop ID</label>
                            <input class="form-control" id="shop_url" name="shop_url" value="vcshopify2.myshopify.com" required>
                        </div>
                        <div class="form-group">
                            <label>Shop Domain</label>
                            <input class="form-control" id="shop_domain" name="shop_domain" value="https://vcshopify2.myshopify.com" required>
                        </div>
                        
                        <div class="form-group hidden">
                            <label>Client ID</label>
                            <input class="form-control" id="client_id" name="client_id" value="379113138681-bd8fqdfa6tt5po3a1hjp9j607hajkt7q.apps.googleusercontent.com">
                        </div>
                            <div class="form-group hidden">
                            <label>Client Secret</label>
                            <input class="form-control" id="client_secret" name="client_secret" value="kRLliQ_aB66pcVyBRUr2Tllg">
                        </div>
                        <div class="form-group hidden">
                            <label>Redirect URL</label>
                            <input class="form-control" id="redirect_url" name="redirect_url" value="' . $current_link . '">
                        </div>
                        <div class="form-group">
                            <label>Merchant ID</label>
                            <input type="number" class="form-control" id="merchant_id" name="merchant_id" value="119488439" required>
                        </div>
                        
                        <div class="form-group hidden">
                            <label>Start Product</label>
                            <input type="number" class="form-control" id="start_product" name="start_product" value="0" required>
                        </div>
                        <div class="form-group">
                            <label>Product Limit</label>
                            <input type="number" class="form-control" id="end_product" name="end_product" value="5" required>
                            <p class="help-block">Enter "0" to process all products.</p>
                        </div>

                        <input type="hidden" id="process_merchantFeed" name="process_merchantFeed">
                        <button type="submit" class="btn btn-success">Go Feed</button>
                        <button type="reset" class="btn btn-default">Reset</button>

                    </div>
                    <div class="col-lg-1">
                    </div>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <label>API Key</label>
                            <input class="form-control" id="shop_api_key" name="shop_api_key" value="af4553f71a3f4a1c2d76d3d3fd3866f4" required>
                        </div>
                        <div class="form-group">
                            <label>API Secret</label>
                            <input class="form-control" id="shop_secret_key" name="shop_secret_key" value="fcf13188c314da4bb8d7f6731bf29218" required>
                        </div>
                        <div class="form-group">
                            <label>Shipping Price</label>
                            <input type="number" class="form-control" id="shipping_price" name="shipping_price" value="0.99" required>
                        </div>
                        <div class="form-group hidden">
                            <label>Shipping Currency</label>
                            <input class="form-control" id="shipping_currency" name="shipping_currency" value="USD" required>
                        </div>
                        <div class="form-group" hidden>
                            <label>Shipping Country</label>
                            <input class="form-control" id="shipping_country" name="shipping_country" value="US" required>
                        </div>
                        <div class="form-group">
                            <label>Shipping Service</label>
                            <input class="form-control" id="shipping_service" name="shipping_service" value="Standard Shipping" required>
                        </div>
                    </div>
                 </form>
                </div>
            </div>
        </div>
    </div>
</div>';
    }
}

function iframe_shopify_feed_page() {

    if (!current_user_can('manage_options') || !isset($_SESSION['shop_url'])) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (ob_get_level() == 0) {
        ob_start();
    }
    // Calculated Time
    $time_all = microtime(true);
    $time_getShopify = 0;
    $time_createFeed = 0;
    $time_requestAPI = 0;
    
    $client = new Google_Client();
    //$client->setAuthConfig('client-secrets02.json');  
    $client->setClientId($_SESSION['client_id']);
    $client->setClientSecret($_SESSION['client_secret']);
    $client->setRedirectUri($_SESSION['redirect_url']);      // redirect uri
    $client->setScopes(Google_Service_ShoppingContent::CONTENT);

    if (isset($_GET['logout'])) { // logout: destroy token
        unset($_SESSION['token']);
        die('Logged out.');
    }

    if (isset($_GET['code'])) { // we received the positive auth callback, get the token and store it in session
        $client->authenticate($_GET['code']);
        $_SESSION['token'] = $client->getAccessToken();
    }

    if (isset($_SESSION['token'])) { // extract token from session and configure client
        $token = $_SESSION['token'];
        $client->setAccessToken($token);
    }

    if (!$client->getAccessToken()) { // auth call to google
        $authUrl = $client->createAuthUrl();
        header("Location: " . $authUrl);
        die;
    }

    // Oauth2 succesful => create Service to call
    $service = new Google_Service_ShoppingContent($client);
    
    echo "OAUTH2 DONE.<br/>";

    $input['shop_url'] = $_SESSION['shop_url'];
    $input['shop_api_key'] = $_SESSION['shop_api_key'];
    $input['shop_secret_key'] = $_SESSION['shop_secret_key'];
    
    // Get Shipping Information
    $shipping['shipping_price'] = $_SESSION['shipping_price'];
    $shipping['shipping_service'] = $_SESSION['shipping_service'];
    
    // Get Shop Domain
    $Shop_URL = $_SESSION['shop_domain'] + '/products/';
    
    $shopifyClient = new Shopify($input['shop_url'], $input['shop_api_key'], $input['shop_secret_key']);

    $parameters = array();

    // Getting products
    echo '#<strong><font color="blue"> GETTING PRODUCTS...</font></strong><br/>';
    ob_flush();
    flush();
    sleep(1);

    // Don't have product id = 0
    if ($_SESSION['start_product'] == 0) {
        $_SESSION['start_product'] = 1;
    }
    
    if ($_SESSION['end_product'] == 0) {
        $total_product = $shopifyClient->getCountProducts();
        $_SESSION['end_product'] = $total_product;
    } else {
        $total_product = $_SESSION['end_product'] - $_SESSION['start_product'] + 1;
    }
    
    $page_start = $_SESSION['start_product'] / MAX_PRODUCT_PAGE + 1;
    $page_start = (int) $page_start;
    
    $page_end = $_SESSION['end_product'] / MAX_PRODUCT_PAGE + 1;
    $page_end = (int) $page_end;

    echo "Preparing the requests .... <br/>";
    ob_flush();
    flush();
    usleep(2);
    $t1 = microtime(true);
    $shopifyClient->initCallProducts(MAX_PRODUCT_PAGE, $page_start, $page_end);
    $t2 = microtime(true);
    $t_init = $t2 - $t1;
    echo "Init call to API DONE in " . number_format($t_init, 2) . 's <br/>';
    
    $countProduct = 0;

    for ($i = $page_start; $i <= $page_end; $i++) {
        
        $parameters = array(
            'limit' => MAX_PRODUCT_PAGE,
            'page' => $i
        );
        
        $t1 = microtime(true);
        $part_products = $shopifyClient->getProducts($parameters);
        $t2 = microtime(true);
        $time_getShopify_1 = $t2 - $t1;
        $time_getShopify = $time_getShopify + $time_getShopify_1;
        
        if ($part_products == false) {
            echo '<strong>Can not get products.</strong><br/>';
            exit;
        } else {
            echo '<strong>Get done ' . count($part_products['products']) . ' products in ' . number_format($time_getShopify_1, 2) . '</strong><br/>';
            
            ob_flush();
            flush();
            usleep(2);
        }
        
        foreach ($part_products['products'] as $product) {
            
            if ($countProduct >= $total_product) {
                break;
            }
            
            $countProduct++;

            $t1 = microtime(true);
            $postBody = createShopifyProductFeed($product, $Shop_URL, $shipping);
            
            $t2 = microtime(true);
            $time_createFeed = $time_createFeed + $t2 - $t1;
            
            $batchEntry = new Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry();
            $batchEntry->setBatchId($countProduct);
            $batchEntry->setMerchantId($_SESSION['merchant_id']); //$merchantID is a string, it works for connection
            $batchEntry->setMethod("insert");
            $batchEntry->setProduct($postBody); //$postBody is a Google_Service_ShoppingContent_Product object
            $batchEntry->setProductId($postBody->getOfferID());
            $entries[] = $batchEntry;

            if ($countProduct % MAX_PRODUCT_BATCH == 0) {
                $batch = new Google_Service_ShoppingContent_ProductsCustomBatchRequest();
                $batch->setEntries($entries);

                $t1 = microtime(true);
                $batchResponse = $service->products->custombatch($batch);
                $t2 = microtime(true);
                $time_requestAPI = $time_requestAPI + $t2 - $t1;

                foreach ($batchResponse->entries as $entry) {
                    if (empty($entry->getErrors())) {
                        $product = $entry->getProduct();
                        printf("Inserted product: %s => Offer ID: %s with %d warnings<br/>", $product->getTitle(), $product->getOfferId(), count($product->getWarnings()));
                    } else {
                        print ("There were errors inserting a product:<br/>");
                        foreach ($entry->getErrors()->getErrors() as $error) {
                            printf("\t%s\n", $error->getMessage());
                        }
                    }
                }

                echo "<strong>REQUESTED " . $countProduct . "<strong><br/>";
                
                $entries = [];

                ob_flush();
                flush();
                usleep(2);

                }
        }
    }
    
    if (!empty($entries)) {
        // request everything
        $batch = new Google_Service_ShoppingContent_ProductsCustomBatchRequest();
        $batch->setEntries($entries);
        
        $t1 = microtime(true);
        $batchResponse = $service->products->custombatch($batch);
        $t2 = microtime(true);
        $time_requestAPI = $time_requestAPI + $t2 - $t1;
        
        foreach ($batchResponse->entries as $entry) {
            if (empty($entry->getErrors())) {
                $product = $entry->getProduct();
                printf("Inserted product: %s => Offer ID: %s with %d warnings<br/>", $product->getTitle(), $product->getOfferId(), count($product->getWarnings()));
            } else {
                print ("There were errors inserting a product:<br/>");
                foreach ($entry->getErrors()->getErrors() as $error) {
                    printf("\t%s\n", $error->getMessage());
                }
            }
        }
        
        echo "<strong>REQUESTED " . $countProduct . "<strong><br/>";
    }
    
    $end_time = microtime(true);
    $all_time = $end_time - $time_all;  

    echo "############################# <br/>";
    echo '<strong><font color="blue">FEED ' . $countProduct . ' PRODUCTS IN ' . number_format($all_time, 2) . 's </font></strong><br/>';
    echo 'Get Shopify Product Time: ' . number_format($time_getShopify, 2) . ' | Create Feed Time: ' . number_format($time_createFeed, 2) . ' | Request API Time: ' . number_format($time_requestAPI, 2) . '<br/>';
    echo '<strong><font color="green">ALL DONE.</font></strong>"';
    
    ob_end_flush();
}

function createShopifyProductFeed($product, $link, $shipping = '') {

    $gProduct = new Google_Service_ShoppingContent_Product();

    if (isset($product['sku']) && !empty($product['sku'])) {
        $_sku = $product['sku'];
    } elseif (isset($product['handle']) && !empty($product['handle'])) {
        $pos = strrpos($product['handle'], "-");
        if ($pos!=false) {
            $_sku = substr($product['handle'], $pos+1);
        }
    } else {
        $_sku = $product['id'];
    }
    
    $gProduct->setOfferId($_sku);
    $gProduct->setTitle($product['title']);
    
    $description = str_replace("\n", '', $product['body_html']);
    $gProduct->setDescription(strip_tags($description));
    
    $product_link = $link . $product['handle'];
    $gProduct->setLink($product_link);
    
    $product['image']['src'] = preg_replace('/\?.*/', '', $product['image']['src']);
    $gProduct->setImageLink($product['image']['src']);
    $gProduct->setContentLanguage('en');
    $gProduct->setTargetCountry('US');
    $gProduct->setChannel('online');
    $gProduct->setAvailability('in stock');
    
    $gProduct->setCondition('new');
    $gProduct->setGoogleProductCategory('Apparel & Accessories > Clothing');
    $gProduct->setColor('Black');

    $gProduct->setSizeSystem('US');
    $gProduct->setSizeType('regular');
    
//    $gProduct->setSizes(array(strtoupper($product['size'])));
    $gProduct->setSizes(array('S','M','L','XL','XXL','3XL','4XL'));
    $gProduct->setGender('unisex');
    $gProduct->setAdult(false);
    $gProduct->setAgeGroup('Adult');
    $gProduct->setBrand('Apparel');

    $variant = $product['variants'][0];
    
    if (isset($variant['compare_at_price'])) {
        $sale_price = new Google_Service_ShoppingContent_Price();
        $sale_price->setValue($variant['price']);
        $sale_price->setCurrency('USD');
        
        $price = new Google_Service_ShoppingContent_Price();
        $price->setValue($variant['compare_at_price']);
        $price->setCurrency('USD');
    } else {
        $price = new Google_Service_ShoppingContent_Price();
        $price->setValue($variant['price']);
        $price->setCurrency('USD');
    }
    
    $gProduct->setPrice($price);
    if (isset($sale_price)) {
        $gProduct->setSalePrice($sale_price);
    }
    
    $shipping_price = new Google_Service_ShoppingContent_Price();
    
    if (isset($shipping['shipping_price'])) {
        $shipping_price->setValue($shipping['shipping_price']);
    } else {
        $shipping_price->setValue(0);
    }
    
    $shipping_price->setCurrency('USD');

    $shipping = new Google_Service_ShoppingContent_ProductShipping();
    $shipping->setPrice($shipping_price);
    $shipping->setCountry('US');
    
    if (isset($shipping['shipping_service'])) {
        $shipping->setService($shipping['shipping_service']);
    } else {
        $shipping->setService('Standard Shipping');
    }

    $gProduct->setShipping(array($shipping));
    
    $tax_price = new Google_Service_ShoppingContent_ProductTax();
    $tax_price->setRate('0');
    $tax_price->setCountry('US');
    $tax_price->setTaxShip(false);
    
    $gProduct->setTaxes(array($tax_price));

    // Max = 10 to avoid Google API disapproved
    $countImage = 0;
    if (isset($product['images']) && count($product['images']) > 0) {
        foreach ($product['images'] as $image) {
            $countImage++;
            $images[] = preg_replace('/\?.*/', '', $image['src']);
            if ($countImage == 10) {
                break;
            }
        }
    }
    
    if (isset($images)) {
        $gProduct->setAdditionalImageLinks($images);
    }
    
    return $gProduct;
}

?>