<?php

/**
 * Plugin Name: WooCommerce Tools
 * Plugin URI: http://minhtuanit.me
 * Description: A small tools to help you manage your WordPress - WooCommerce
 * Version: 2.00
 * Author: Tuan Dao
 * Author URI: http://minhtuanit.me
 * License: GPL2
 * Created On: 11-01-2017
 * Updated On: 12-05-2017
 */
// Define WC_PLUGIN_DIR.
if (!defined('WC_PLUGIN_DIR')) {
    define('WC_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Define WC_PLUGIN_FILE.
if (!defined('WC_PLUGIN_URL')) {
    define('WC_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once('includes/autoload.php' );

add_action('plugins_loaded', 'woocommerce_tools_plugin_init');

function woocommerce_tools_plugin_init() {
    add_action('admin_menu', 'woocommerce_tools_admin_menu');
    add_action('login_init', 'send_frame_options_header', 10, 0);
    add_action('admin_init', 'send_frame_options_header', 10, 0);
}

function woocommerce_tools_admin_menu() {

    add_menu_page('Woo Tools', 'Woo Tools', 'manage_options', 'variation-products-main', 'variation_products_main_page', 'dashicons-welcome-widgets-menus', 3);
    add_submenu_page('variation-products-main', __('Woo Converter'), __('Woo Converter'), 'manage_options', 'variation-products-main');
    add_submenu_page('variation-products-main', __('Woo Feed'), __('Woo Feed'), 'manage_options', 'function-woocommerce-feed', 'function_woocommerce_merchant_feed_page');
    add_submenu_page('variation-products-main', __('Shopify Feed'), __('Shopify Feed'), 'manage_options', 'function-shopify-feed', 'shopify_feed_main_page');
//    add_submenu_page('variation-products-main', __('Function Test'), __('Function Test'), 'manage_options', 'function-test', 'function_insert_test_page');
// Tools
    add_submenu_page('variation-products-main', __('Tool Options'), __('Tool Options'), 'manage_options', 'tool-options', 'tool_option_page');

    add_submenu_page('tool-options', __('Delete all merchant'), __('Delete all merchant'), 'manage_options', 'delete-all-merchant-products', 'delete_all_merchant_products');
    add_submenu_page('tool-options', __('Delete single merchant'), __('Delete single merchant'), 'manage_options', 'delete-single-merchant-product', 'delete_single_merchant_product');
// Add iframe to a sub-menu to hide
    add_submenu_page('tool-options', __('Iframe Insert Variations'), __('Iframe Insert Variations'), 'manage_options', 'iframe-insert-variations', 'iframe_insert_variations_page');
    add_submenu_page('tool-options', __('Iframe Feed Merchant'), __('Iframe Feed Merchant'), 'manage_options', 'iframe-feed-merchant', 'iframe_feed_merchant_page');
    add_submenu_page('tool-options', __('Iframe Shopify Feed Merchant'), __('Iframe Shopify Feed Merchant'), 'manage_options', 'iframe-shopify-feed-merchant', 'iframe_shopify_feed_page');

}

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
 * Testing
 */

function insert_variation_products_page() {
    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>Insert Products</h2>';
    echo '</div>';
//    function_insert_variation_products();
}

/*
 * Testing
 */

function function_insert_test_page() {

    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>TEST INSERT</h2>';
    echo '</div>';

    $rawData = file_get_contents(plugin_dir_path(__FILE__) . 'input/3products.json'); // Get json from sample file

    $products = json_decode($rawData, true);

    $link = 'http://vcshopify2.myshopify.com/products/';

    foreach ($products['products'] as $product) {
        $test = createShopifyProductFeed($product, $link);
    }


    echo "INSERT TEST DONE";
}

function tool_option_page() {

    prefix_enqueue();

    $current_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";

    echo '';

    echo '<div class="wrap">
            <div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Google Merchant Manager Tools  
                        </div>
                        <div class="panel-body">';

    if (isset($_POST['process_clearSession'])) {
        
        session_unset();
        session_destroy();
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        echo '  <div class="alert alert-success">
                    <strong>The SESSION has been deleted. Go continue your job.</strong>
                </div>';
        echo '  </div></div></div></div></div>';
        
    } else {

        echo '  <form method="POST" action="' . $current_link . '?page=delete-single-merchant-product">
                    <p>
                        <button type="submit" class="btn btn-warning btn-lg btn-block">
                            Delete Single Merchant Product
                        </button>
                    </p>
                </form>
                <form method="POST" action="' . $current_link . '?page=delete-all-merchant-products">
                    <p>
                        <button type="submit" class="btn btn-danger btn-lg btn-block">
                            Delete All Merchant Products
                        </button>
                    </p>
                </form>
            </div>
            </div>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            The Other Tools
        </div>
        <div class="panel-body">
            <form method="POST">
                <p>Use this to clear the session and get another Oauth2.</p>
                <input type="hidden" id="process_clearSession" name="process_clearSession">
                <p><button type="submit" class="btn btn-primary btn-lg btn-block">Clear Session</button></p>
            </form>
        </div>
        </div>
        </div>
    </div>';
    }
}



?>