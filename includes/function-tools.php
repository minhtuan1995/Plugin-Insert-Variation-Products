<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
                                        <button type="submit" class="btn btn-danger">Go Delete</button>
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
                                        <button type="submit" class="btn btn-danger">Go Delete</button>
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