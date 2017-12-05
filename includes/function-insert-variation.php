<?php

function variation_products_main_page() {
    if (ob_get_level() == 0)
        ob_start();
    prefix_enqueue();

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';

    if (isset($_POST['process_conectToShopify'])) {
        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>WooCommerce Converter:</strong> Getting Products from Shopify...
                        </div>
                        <div class="panel-body">';

        $_SESSION['shop_url'] = $_POST['shop_url'];
        $_SESSION['shop_api_key'] = $_POST['shop_api_key'];
        $_SESSION['shop_secret_key'] = $_POST['shop_secret_key'];
        
        $shopifyClient = new Shopify($_SESSION['shop_url'], $_SESSION['shop_api_key'], $_SESSION['shop_secret_key']);

        $checkConnection = $shopifyClient->getCountProducts();
        $_SESSION['total_products'] = $checkConnection;
        if ($checkConnection == FALSE) {
            echo '<div class="alert alert-danger">
                        <strong>Error 01: Something went wrong, please help to check your input information.</strong>
                    </div>';
        } else {
            echo '<div class="alert alert-success">
                        <strong>Connect to Shopify successful. Your total products: <font color="red">' . $checkConnection . '</font></strong>
                </div>';
            echo '<div class="row">
                        <div class="col-lg-12">
                            <form role="form" method="post">
                                <div class="form-group">
                                    <label>Number Products Per Page</label>
                                    <select class="form-control" id="shop_limit" name="shop_limit">
                                        <option value="3">3</option>
                                        <option value="10">10</option>
                                        <option value="50">50</option>
                                        <option value="100" selected>100</option>
                                        <option value="150">150</option>
                                        <option value="200">200</option>
                                        <option value="250">250</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Start Product</label>
                                    <input type="number" class="form-control" id="start_product" name="start_product" value="1" required>
                                </div>
                                <div class="form-group">
                                    <label>End Product</label>
                                    <input type="number" class="form-control" id="end_product" name="end_product" value="10" required>
                                    <p class="help-block">Enter "0" to process all products (' . $checkConnection . ')</p>
                                </div>
                                
                                <input type="hidden" id="process_wooConvert" name="process_wooConvert">

                                <button type="submit" class="btn btn-success">Get Products and Import</button>
                                <button type="reset" class="btn btn-default">Reset</button>
                            </form>
                        </div>
                    </div>';
        }
        echo '</div></div></div></div>';
    } elseif (isset($_POST['process_wooConvert'])) {
        prefix_enqueue();

        // MAIN PROCESS 
        $_SESSION['shop_limit'] = $_POST['shop_limit'];
        $_SESSION['start_product'] = $_POST['start_product'];
        $_SESSION['end_product'] = $_POST['end_product'];
        
        create_iframe('iframe-insert-variations', 'WooCommerce Converter', 'Importing...');
        
    } else {
        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>WooCommerce Converter:</strong> Connect to Shopify</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form role="form" method="post">
                                        <div class="form-group">
                                            <label>Shop ID</label>
                                            <input class="form-control" id="shop_url" name="shop_url" value="vcshopify2.myshopify.com">
                                        </div>
                                        <div class="form-group">
                                            <label>API Key</label>
                                            <input class="form-control" id="shop_api_key" name="shop_api_key" value="af4553f71a3f4a1c2d76d3d3fd3866f4">
                                        </div>
                                        <div class="form-group">
                                            <label>API Secret</label>
                                            <input class="form-control" id="shop_secret_key" name="shop_secret_key" value="fcf13188c314da4bb8d7f6731bf29218">
                                        </div>
                                        <input type="hidden" id="process_conectToShopify" name="process_conectToShopify">
                                        <button type="submit" class="btn btn-success">Connect to Shopify</button>
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
    }

//    session_write_close(); 
}

function iframe_insert_variations_page() {

    if (!current_user_can('manage_options') || !isset($_SESSION['shop_url'])) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    insert_variation_prepare();
    // Init time
    $all_time = 0;
    $time_getShopify = 0;
    $start_time = microtime(true);
    
    $input['shop_url'] = $_SESSION['shop_url'];
    $input['shop_api_key'] = $_SESSION['shop_api_key'];
    $input['shop_secret_key'] = $_SESSION['shop_secret_key'];
    $PRODUCT_PER_PAGE = $_SESSION['shop_limit'];
//    $input['shop_page'] = $_SESSION['shop_page'];
    
    $shopifyClient = new Shopify($input['shop_url'], $input['shop_api_key'], $input['shop_secret_key']);

    $parameters = array();

    // Getting products
    echo '<br/>#<strong><font color="blue"> WOOECOMMERCE IMPORTING...</font></strong><br/>';
    ob_flush();
    flush();
    sleep(1);

    // Don't have product id = 0
    if ($_SESSION['start_product'] == 0) {
        $_SESSION['start_product'] = 1;
    }
    
    if ($_SESSION['end_product'] == 0) {
        if (isset($_SESSION['total_products'])) {
            $total_product = $_SESSION['total_products'];
        } else {
            $total_product = $shopifyClient->getCountProducts();
        }
        $_SESSION['end_product'] = $total_product;
    } else {
        $total_product = $_SESSION['end_product'] - $_SESSION['start_product'] + 1;
    }
    
    $page_start = $_SESSION['start_product'] / $PRODUCT_PER_PAGE + 1;
    $page_start = (int) $page_start;
    
    $page_end = $_SESSION['end_product'] / $PRODUCT_PER_PAGE + 1;
    $page_end = (int) $page_end;

    echo "Preparing the requests .... <br/>";
    ob_flush();
    flush();
    usleep(2);
    $t1 = microtime(true);
    $shopifyClient->initCallProducts($PRODUCT_PER_PAGE, $page_start, $page_end);
    $t2 = microtime(true);
    $t_init = $t2 - $t1;
    echo "Initialize call to API DONE in " . number_format($t_init, 2) . 's <br/>';
    
    $countProduct = 0;

    for ($i = $page_start; $i <= $page_end; $i++) {
        
        $parameters = array(
            'limit' => $PRODUCT_PER_PAGE,
            'page' => $i
        );
        
        $t1 = microtime(true);
        $part_products = $shopifyClient->getProducts($parameters);
        $t2 = microtime(true);
        $ti = $t2 - $t1;
        $time_getShopify += $ti;
        
        echo "<br/> ==================== <br/>";
        echo ' => Getting ' . count($part_products['products']) . ' products in ' . number_format($ti, 2) .'s' ;
        echo "<br/> ==================== <br/>";
        
        if ($part_products == false) {
            echo '<strong>Error: Cant not get product of page ' . $i . '. Pls help to check.</strong>';
            continue;
        }

        $countProduct = function_insert_variation_products($part_products['products'], $countProduct, $total_product);
        
    }
    
    $end_time = microtime(true);
    $all_time = $end_time - $start_time;  

    echo "<br/>############################# <br/>";
    echo '<strong><font color="blue">IMPORTED ' . $countProduct . ' PRODUCTS IN ' . number_format($all_time, 2) . 's </font></strong><br/>';
    echo "Get Shopify products time: " . number_format($time_getShopify, 2) . "s <br/>";
    echo '<strong><font color="green">ALL DONE.</font></strong>"';
    
    insert_variation_finish();
}
/*
 * MAIN FUNCTION - Create products
 */
function function_insert_variation_products($all_products, $countProduct = 0, $total_product) {
    
    foreach ($all_products as $single_product) {
        $countProduct++;
        // START - Process single product
        $t1 = microtime(true);

        $product_data = jsonConvert($single_product);
        echo 'Importing: <strong><font color="blue">' . $product_data['sku'] . '</font></strong> | ' . $product_data['name'] . '<br/>';
        
        //Update product if exists
        $productId = wc_get_product_id_by_sku($product_data['sku']);
        
        if ($productId != 0) {  // Update If exists
            $productExist = get_post($productId)->to_array();

            $single_product['updated_at'] = mysql2date('Y-m-d H:i:s', $single_product['updated_at']);

            if (strtotime($productExist['post_date_gmt']) == strtotime($single_product['updated_at'])) {
                echo '<font color="green"><strong>Product exist already...-> No Change From Source</strong></font><br/>';
            } else {
                echo '<font color="green"><strong>Product exist already...-> Updating </strong></font><br/>';
                update_product($productId, $product_data);
            }
        } ELSE {    // Create new product
            echo '<font color="blue"><strong>Create product...</strong></font><br/>';
            try {
                insert_product($product_data);
            } catch (Exception $ex) {
                echo "<pre>";
                print_r($ex);
                echo "<pre>";
            }
        }
        
        $t2 = microtime(true);
        $t3 = $t2 - $t1;
        echo "Done " . $countProduct . "/" . $total_product . " | ";
        echo " Time: " . number_format($t3, 3) . "s";
        echo "<br/> ==================== <br/>";

        if ($countProduct % 3 == 0) {
            ob_flush();
            flush();
            usleep(2);
        }
        if ($countProduct >= $total_product) {
            return $countProduct;
        }
        // END - Process single product
    }
    
    return $countProduct;
}

function insert_variation_prepare() {
    global $wpdb;
    wp_defer_term_counting( true );
    wp_defer_comment_counting( true );
    
    if (!defined('WP_IMPORTING')) {
        define( 'WP_IMPORTING', true );
    }
    
    $wpdb->query( 'SET autocommit = 0;' );
    
    if (ob_get_level() == 0) {
        ob_start();
    }
}

function insert_variation_finish() {
    global $wpdb;
    ob_end_flush();
    wp_defer_term_counting( false );
    wp_defer_comment_counting( false );
    $wpdb->query( 'COMMIT;' );
}


