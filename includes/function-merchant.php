<?php

require_once( WC_PLUGIN_DIR . '/google-api/vendor/autoload.php' );
//require_once('/google-api/vendor/autoload.php' );

if (!defined('MAX_PRODUCT_BATCH')) {
    define('MAX_PRODUCT_BATCH', 250); 
}
function function_merchant_feed_page() {
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (ob_get_level() == 0)
        ob_start();

    prefix_enqueue();
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<h4 class="page-header">Google Merchant Feed</h4>';
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
        
        echo '<div style="overflow: hidden; margin: 0px; max-width: 1100px;">
        <iframe id="iframe-feed-merchant" class="iframe-feed-merchant" scrolling="yes" src="admin.php?page=iframe-feed-merchant" style="border: 0px none; margin-left: -170px; height: 500px; margin-top: -100px; margin-bottom: -50px; width: 1180px;">
        </iframe></div>';
                
    } else {
//        if (isset($_SESSION)) {
//            session_destroy();
//        }
        $current_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">Connect to Google</div>
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
                                        <button type="submit" class="btn btn-default">Go Feed</button>
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

function delete_all_merchant_products() {
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (ob_get_level() == 0)
        ob_start();

    prefix_enqueue();
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<h3 class="page-header">DELETE Google Merchant Products</h3>';
    echo '<div class="wrap">';

    if (isset($_POST['process_DeleteAllMerchant']) || isset($_GET['code'])) {
        
        if (isset($_POST['client_id'])) {
            $_SESSION['client_id'] = $_POST['client_id'];
            $_SESSION['client_secret'] = $_POST['client_secret'];
            $_SESSION['redirect_url'] = $_POST['redirect_url'];
            $_SESSION['merchant_id'] = $_POST['merchant_id'];
            if (isset($_POST['files_per_page']) && $_POST['files_per_page'] != 0) {
                $_SESSION['files_per_page'] = $_POST['files_per_page'];
            }

            if (isset($_POST['pages_number']) && $_POST['pages_number'] != 0) {
                $_SESSION['pages_number'] = $_POST['pages_number'];
            }
            
            if (isset($_POST['delete_localhost'])) {
                $_SESSION['delete_localhost'] = $_POST['delete_localhost'];
            }
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
        
        echo '#<strong><font color="red"> DELETING PRODUCT MERCHANT...</font></strong><br/>';
        ob_flush();
        flush();
        sleep(1);
        
        $service = new Google_Service_ShoppingContent($client);
    
        // products that we inserted, to demonstrate paging.
        $parameters = array('maxResults' => $_SESSION['files_per_page'], 'includeInvalidInsertedItems' => true);
        $products = $service->products->listProducts(
                $_SESSION['merchant_id'], $parameters);

        $count = 0;
        // You can fetch all items in a loop. We limit to looping just 3
        // times for this example as it may take a long time to finish if you
        // already have many products.
        $offerIds = [];
        $countProduct = 0;
        while (!empty($products->getResources()) && $count++ < $_SESSION['pages_number']) {
            foreach ($products->getResources() as $product) {

                printf("%s : %s <br/>", $product->getId(), $product->getTitle());

                if ($_SESSION['delete_localhost'] == 0) {
                    $countProduct++;
                    $offerIds[] = $product->getId();
                } else {
                    if (strpos($product->getLink(), 'localhost')!=false) {
                        echo $product->getLink() . "<br/>";
                        $countProduct++;
                        $offerIds[] = $product->getId();
                    }
                }
                
    //            $service->products->delete($_SESSION['merchant_id'], $product->getId());

                if ($countProduct % 200 == 0 && $countProduct!= 0) {
                    deleteProductBatch($service, $offerIds);
                    $offerIds = [];
                    echo "<strong>DELETED " . $countProduct . " products.</strong><br/>";
                }
            }
            // If the result has a nextPageToken property then there are more pages
            // available to fetch
            if (empty($products->getNextPageToken())) {
                break;
            }
            // You can fetch the next page of results by setting the pageToken
            // parameter with the value of nextPageToken from the previous result.
            $parameters['pageToken'] = $products->nextPageToken;
            $products = $service->products->listProducts(
                    $_SESSION['merchant_id'], $parameters);
        }

        if (!empty($offerIds)) {
                deleteProductBatch($service, $offerIds);
                echo "DELETED " . $countProduct . " products.";
        }

        echo "ALL DONE.";
        
    } else {
//        if (isset($_SESSION)) {
//            session_destroy();
//        }
        $current_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">Connect to Google</div>
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
                                            <label>Files Per Page</label>
                                            <input class="form-control" id="files_per_page" name="files_per_page" value="20">
                                        </div>
                                        <div class="form-group">
                                            <label>Number of Pages</label>
                                            <input class="form-control" id="pages_number" name="pages_number" value="20">
                                        </div>
                                        <div class="form-group">
                                            <label>Delete localhost products</label>
                                            <select class="form-control" id="delete_localhost" name="delete_localhost">
                                                <option value="0" selected>No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                        <input type="hidden" id="process_DeleteAllMerchant" name="process_DeleteAllMerchant">
                                        <button type="submit" class="btn btn-default">Go Delete</button>
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

function delete_single_merchant_product() {
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (ob_get_level() == 0)
        ob_start();

    prefix_enqueue();
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<h3 class="page-header">DELETE Single Google Merchant Product</h3>';
    echo '<div class="wrap">';

    if (isset($_POST['process_OauthGoogle']) || isset($_GET['code'])) {
        
        if (isset($_POST['client_id'])) {
            $_SESSION['client_id'] = $_POST['client_id'];
            $_SESSION['client_secret'] = $_POST['client_secret'];
            $_SESSION['redirect_url'] = $_POST['redirect_url'];
            $_SESSION['merchant_id'] = $_POST['merchant_id'];
            if (isset($_POST['product_id']) && $_POST['product_id'] != 0) {
                $_SESSION['product_id'] = $_POST['product_id'];
            }

//            if (isset($_POST['end_product']) && $_POST['end_product'] != 0) {
//                $_SESSION['end_product'] = $_POST['end_product'];
//            }
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
        
        echo '#<strong><font color="red"> DELETE SINGLE PRODUCT MERCHANT...</font></strong><br/>';
        ob_flush();
        flush();
        sleep(1);
        
        $service = new Google_Service_ShoppingContent($client);
    
        $ListID = explode(" ", $_SESSION['product_id']);
        
        foreach ($ListID as $key => $value) {
            
            $ProductId = buildProductId($value);

            $products = $service->products->get($_SESSION['merchant_id'], $ProductId);
            $result = $service->products->delete($_SESSION['merchant_id'], $ProductId);
            
        }
        echo "DONE.";
        
    } else {
//        if (isset($_SESSION)) {
//            session_destroy();
//        }
        $current_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">Connect to Google</div>
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
                                            <label>Product ID</label>
                                            <input class="form-control" id="product_id" name="product_id" value="">
                                        </div>
                                        
                                        <input type="hidden" id="process_OauthGoogle" name="process_OauthGoogle">
                                        <button type="submit" class="btn btn-default">Go Delete</button>
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

function deleteProductBatch($service, $offerIds) {
    $entries = [];

    foreach ($offerIds as $key => $offerId) {
      $entry =
          new Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry();
      $entry->setMethod('delete');
      $entry->setBatchId($key);
      $entry->setProductId($offerId);
      $entry->setMerchantId($_SESSION['merchant_id']);

      $entries[] = $entry;
    }

    $batchRequest =
        new Google_Service_ShoppingContent_ProductsCustomBatchRequest();
    $batchRequest->setEntries($entries);

    $batchResponses =
        $service->products->custombatch($batchRequest);
    $errors = 0;
    foreach ($batchResponses->entries as $entry) {
      if (!empty($entry->getErrors())) {
        $errors++;
      }
    }
    print "Requested delete of batch inserted test products<br/>";
    printf("There were %d errors<br/>", $errors);
    ob_flush();
    flush();
    usleep(2);
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

function createProductFeed($product_raw) {

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

    $shipping = new Google_Service_ShoppingContent_ProductShipping();
    $shipping->setPrice($shipping_price);
    $shipping->setCountry('US');
    $shipping->setService('Standard shipping');

    $product->setPrice($price);
    if (isset($sale_price)) {
        $product->setSalePrice($sale_price);
    }

    $product->setShipping(array($shipping));

    return $product;
}

function buildProductId($offerId) {
    return sprintf('%s:%s:%s:%s', 'online', 'en', 'US', $offerId);
}

?>