<?php

require_once( WC_PLUGIN_DIR . '/google-api/vendor/autoload.php' );
//require_once('/google-api/vendor/autoload.php' );

if (!defined('MAX_PRODUCT_BATCH')) {
    define('MAX_PRODUCT_BATCH', 250); 
}
function function_woocommerce_merchant_feed_page() {
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (ob_get_level() == 0) {
        ob_start();
    }
    
    prefix_enqueue();
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';

    if (isset($_POST['process_OauthGoogle']) || isset($_GET['code'])) {
        
        if (isset($_POST['client_id'])) {
            $_SESSION['client_id'] = $_POST['client_id'];
            $_SESSION['client_secret'] = $_POST['client_secret'];
            $_SESSION['redirect_url'] = $_POST['redirect_url'];
            $_SESSION['merchant_id'] = $_POST['merchant_id'];
            if (isset($_POST['start_product']) && $_POST['start_product'] != 0) {
                $_SESSION['start_product'] = $_POST['start_product'];
            } else {
                unset($_SESSION['start_product']);
            }

            if (isset($_POST['end_product']) && $_POST['end_product'] != 0) {
                $_SESSION['end_product'] = $_POST['end_product'];
            } else {
                unset($_SESSION['end_product']);
            }
        }
        
        $client = new Google_Client();
        //$client->setAuthConfig('client-secrets02.json');  
        $client->setClientId($_SESSION['client_id']);
        $client->setClientSecret($_SESSION['client_secret']);
        $client->setRedirectUri($_SESSION['redirect_url']);      // redirect uri
        $client->setScopes(Google_Service_ShoppingContent::CONTENT);

        //$client->setUseBatch(true);

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
        
        create_iframe('iframe-feed-merchant', 'WooCommerce Feed', 'Requesting...');
        
    } else {

        $current_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>WooCommerce Feed:</strong> Connect to Google</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form role="form" method="post">
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
                                            <input class="form-control" id="merchant_id" name="merchant_id" value="119062116">
                                        </div>
                                        <div class="form-group">
                                            <label>Start Product</label>
                                            <input class="form-control" id="start_product" name="start_product" value="1">
                                        </div>
                                        <div class="form-group">
                                            <label>End Product</label>
                                            <input class="form-control" id="end_product" name="end_product" value="1">
                                            <p class="help-block">Enter "0" to process all products.</p>
                                        </div>

                                        <input type="hidden" id="process_OauthGoogle" name="process_OauthGoogle">
                                        <button type="submit" class="btn btn-success">Go Feed</button>
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }
}

function iframe_feed_merchant_page() {
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

    echo '#<strong><font color="red"> START FEEDING...</font></strong><br/>';
    ob_flush();
    flush();
    sleep(1);

    $start_time = microtime(true);

    $service = new Google_Service_ShoppingContent($client);

    $wooFeed = new WooProducts(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $products = $wooFeed->getAllProducts();
    
    $countProduct = 0;
    $countBatch = 0;
    
    $created_time = 0;
    $created_feed_time = 0;
    $batch_time = 0;
    
    foreach ($products as $product) {
        $countProduct++;
        if (isset($_SESSION['start_product']) && $countProduct < $_SESSION['start_product']) {
            continue;
        }
        if (isset($_SESSION['end_product']) && $countProduct > $_SESSION['end_product']) {
            $countProduct = $_SESSION['end_product'] - $_SESSION['start_product'] + 1;
            break;
        }
        $t1 = microtime(true);
        $variations = $wooFeed->getAllVariations($product);
        $t2 = microtime(true);
        $created_time = $created_time + $t2 - $t1;
        
//        foreach ($variations as $variation) {
        $variation = $variations;
            $countBatch++;
            
            $t1 = microtime(true);
            $postBody = createProductFeed($variation);
            $t2 = microtime(true);
            $created_feed_time = $created_feed_time + $t2 - $t1;
            
            $batchEntry = new Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry();
            $batchEntry->setBatchId($countBatch);
            $batchEntry->setMerchantId($_SESSION['merchant_id']); //$merchantID is a string, it works for connection
            $batchEntry->setMethod("insert");
            $batchEntry->setProduct($postBody); //$postBody is a Google_Service_ShoppingContent_Product object
            $batchEntry->setProductId($variation['sku']);
            $entries[] = $batchEntry;
            
            if ($countBatch % MAX_PRODUCT_BATCH == 0) {
                $batch = new Google_Service_ShoppingContent_ProductsCustomBatchRequest();
                $batch->setEntries($entries);
                
                $t1 = microtime(true);
                
                $batchResponse = $service->products->custombatch($batch);
                
//                $service->products->custombatch($batch);
                $t2 = microtime(true);
                $batch_time = $batch_time + $t2 - $t1;
                
                echo "REQUESTED " . $countBatch . "<br/>";
                
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

                $entries = [];

                ob_flush();
                flush();
                usleep(2);
            }
//        }
    }

    if (!empty($entries)) {
        // request everything
        $batch = new Google_Service_ShoppingContent_ProductsCustomBatchRequest();
        $batch->setEntries($entries);
//        $service->products->custombatch($batch);
        $batchResponse = $service->products->custombatch($batch);
        
        echo "REQUESTED " . $countBatch . "<br/>";
        
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
    }

    $end_time = microtime(true);
    $time = $end_time - $start_time;

    echo "Feed " . $countProduct . " products in " . number_format($time, 2) . "s <br/>";
    echo "CREATE TIME: " . $created_time . " | CREATE FEED TIME: " . $created_feed_time . " | REQUEST TIME: " . $batch_time;
    echo "<br/>ALL DONE.";

    ob_end_flush();
}

function createProductFeed($product_raw, $shipping) {

    $product = new Google_Service_ShoppingContent_Product();

    $product->setOfferId($product_raw['sku']);
    $product->setTitle($product_raw['post_title']);
    $product->setDescription(strip_tags($product_raw['description']));
    $product->setLink($product_raw['link']);
    $product->setImageLink($product_raw['image_link']);
    $product->setContentLanguage('en');
    $product->setTargetCountry('US');
    $product->setChannel('online');
    $product->setAvailability('in stock');
    $product->setAdditionalImageLinks($product_raw['additional_image_link']);
    $product->setCondition('new');
    $product->setGoogleProductCategory('Apparel & Accessories > Clothing');
    $product->setColor($product_raw['color']);
    
    $product->setSizeSystem('US');
    $product->setSizeType('regular');
//    $product->setSizes(array(strtoupper($product_raw['size'])));
    $product->setSizes(array('S','M','L','XL','XXL','3XL','4XL'));
    $product->setGender($product_raw['gender']);

    $product->setAdult(false);

    $product->setAgeGroup('adult');

    $product->setBrand('Apparel');

    if (isset($product_raw['sale_price'])) {
        $sale_price = new Google_Service_ShoppingContent_Price();
        $sale_price->setValue($product_raw['sale_price']);
        $sale_price->setCurrency('USD');
    }

    if (isset($product_raw['regular_price'])) {
        $price = new Google_Service_ShoppingContent_Price();
        $price->setValue($product_raw['regular_price']);
        $price->setCurrency('USD');
    } else {
        $price = new Google_Service_ShoppingContent_Price();
        $price->setValue($product_raw['price']);
        $price->setCurrency('USD');
    }

    $shipping_price = new Google_Service_ShoppingContent_Price();
    $shipping_price->setValue('0.99');
    $shipping_price->setCurrency('USD');

    $product->setPrice($price);
    if (isset($sale_price)) {
        $product->setSalePrice($sale_price);
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

    $product->setShipping(array($shipping));
    
    $tax_price = new Google_Service_ShoppingContent_ProductTax();
    $tax_price->setRate('0');
    $tax_price->setCountry('US');
    $tax_price->setTaxShip(false);
    
    $product->setTaxes(array($tax_price));  

    return $product;
}

function buildProductId($offerId) {
    return sprintf('%s:%s:%s:%s', 'online', 'en', 'US', $offerId);
}

?>