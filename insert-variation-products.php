<?php

/**
 * Plugin Name: Insert Variation Products
 * Plugin URI: http://minhtuanit.me
 * Description: Add a variable product to WP. Errors are written to wp-admin/insert_product_logs.txt file.
 * Version: 1.00
 * Author: Tuan Dao
 * Author URI: http://minhtuanit.me
 * License: GPL2
 * Created On: 11-01-2017
 * Updated On: 11-01-2017
 */
// Define WC_PLUGIN_DIR.
if (!defined('WC_PLUGIN_DIR')) {
    define('WC_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Define WC_PLUGIN_FILE.
if (!defined('WC_PLUGIN_URL')) {
    define('WC_PLUGIN_URL', plugin_dir_url(__FILE__));
}

define('MAX_PRODUCT_PAGE', 50);     // Number of product when process all products
//define('BATCH_SIZE', 25);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once('includes/autoload.php' );

add_action('plugins_loaded', 'variations_plugin_init');

function variations_plugin_init() {
    add_action('admin_menu', 'variation_products_admin_menu');
    add_action('login_init', 'send_frame_options_header', 10, 0);
    add_action('admin_init', 'send_frame_options_header', 10, 0);
}

function variation_products_admin_menu() {

    add_menu_page('Woo Convert', 'Woo Convert', 'manage_options', 'variation-products-main', 'variation_products_main_page', 'dashicons-welcome-widgets-menus', 3);
    add_submenu_page('variation-products-main', __('Merchant Feed'), __('Merchant Feed'), 'manage_options', 'function-merchant-feed', 'function_merchant_feed_page');
// Tools
    add_submenu_page('variation-products-main', __('Tool Options'), __('Tool Options'), 'manage_options', 'tool-options', 'tool_option_page');

    add_submenu_page('tool-options', __('Delete all merchant'), __('Delete all merchant'), 'manage_options', 'delete-all-merchant-products', 'delete_all_merchant_products');
    add_submenu_page('tool-options', __('Delete single merchant'), __('Delete single merchant'), 'manage_options', 'delete-single-merchant-product', 'delete_single_merchant_product');
// Add iframe to a sub-menu to hide
    add_submenu_page('tool-options', __('Iframe Insert Variations'), __('Iframe Insert Variations'), 'manage_options', 'iframe-insert-variations', 'iframe_insert_variations_page');
    add_submenu_page('tool-options', __('Iframe Feed Merchant'), __('Iframe Feed Merchant'), 'manage_options', 'iframe-feed-merchant', 'iframe_feed_merchant_page');


//add_submenu_page('variation-products-main',
//			 __('Export XML'),
//			 __('Export XML') ,
//			 'manage_options',
//			 'function-export-xml',
//			 'function_export_xml_page');
}

function variation_products_main_page() {
    if (ob_get_level() == 0)
        ob_start();
    prefix_enqueue();

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<h4 class="page-header">WOO CONVERT</h4>';
    echo '<div class="wrap">';

    if (isset($_POST['process_conectToShopify'])) {
        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Getting Products from Shopify...
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
                                        <option value="100">100</option>
                                        <option value="150">150</option>
                                        <option value="200">200</option>
                                        <option value="250" selected>250</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Page</label>
                                    <input class="form-control" id="shop_page" name="shop_page" value="1">
                                    <p class="help-block">Enter "0" to process all products (' . $checkConnection . ')</p>
                                </div>

                                <input type="hidden" id="process_wooConvert" name="process_wooConvert">

                                <button type="submit" class="btn btn-default">Get Products and Convert</button>
                                <button type="reset" class="btn btn-default">Reset</button>
                            </form>
                        </div>
                    </div>';
        }
        echo '</div></div></div></div>';
    } elseif (isset($_POST['process_wooConvert'])) {
        prefix_enqueue();
        prefix_enqueue_insert_variation();

        if ($_POST['shop_page'] == '') {
            echo '<strong>Error 02: You are not input "Page" number, please F5 and try again.</strong><br/>';
            exit;
        }

        // MAIN PROCESS 
        $_SESSION['shop_limit'] = $_POST['shop_limit'];
        $_SESSION['shop_page'] = $_POST['shop_page'];

        $input['shop_url'] = $_SESSION['shop_url'];
        $input['shop_api_key'] = $_SESSION['shop_api_key'];
        $input['shop_secret_key'] = $_SESSION['shop_secret_key'];
        $input['shop_limit'] = $_SESSION['shop_limit'];
        $input['shop_page'] = $_SESSION['shop_page'];

        echo '<div style="overflow: hidden; margin: 0px; max-width: 1100px;">
                <iframe id="iframe-insert-variations" class="iframe-insert-variations" scrolling="yes" src="admin.php?page=iframe-insert-variations" style="border: 0px none; margin-left: -170px; height: 500px; margin-top: -100px; margin-bottom: -50px; width: 1180px;">
        </iframe></div>';
    } else {
        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">Connect to Shopify</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <form role="form" method="post">
                                        <div class="form-group">
                                            <label>Link Shop</label>
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
                                        <button type="submit" class="btn btn-default">Connect to Shopify</button>
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

    $input['shop_url'] = $_SESSION['shop_url'];
    $input['shop_api_key'] = $_SESSION['shop_api_key'];
    $input['shop_secret_key'] = $_SESSION['shop_secret_key'];
    $input['shop_limit'] = $_SESSION['shop_limit'];
    $input['shop_page'] = $_SESSION['shop_page'];

    $shopifyClient = new Shopify($input['shop_url'], $input['shop_api_key'], $input['shop_secret_key']);

    $parameters = array();

    $parameters['limit'] = $input['shop_limit'];
    $parameters['page'] = $input['shop_page'];


    // Getting products
    echo '#<strong><font color="blue"> GETTING PRODUCTS...</font></strong>';
    ob_flush();
    flush();
    sleep(1);

    if (!empty($parameters['page']) && $parameters['page'] != 0) {

        $products = $shopifyClient->getProducts($parameters);

        if ($products == false) {
            echo '<strong>Error 03: Cannot get products.</strong><br/>';
            exit;
        }

        function_insert_variation_products($products['products']);
        
    } else {
        $maxPage = $_SESSION['total_products'] / MAX_PRODUCT_PAGE + 1;
        $maxPage = (int) $maxPage;

        for ($i = 1; $i <= $maxPage; $i++) {
            $parameters = array(
                'limit' => MAX_PRODUCT_PAGE,
                'page' => $i
            );
            $part_products = $shopifyClient->getProducts($parameters);

            if ($part_products == false) {
                echo '<div class="alert alert-danger">
                    <strong>Error 01: Something went wrong, please help to check your input information.</strong>
                </div>';
                echo '</div></div></div></div>';
                exit;
            }

            function_insert_variation_products($part_products['products'], $i, $maxPage);
        }
    }
    
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

    $rawData = file_get_contents(plugin_dir_path(__FILE__) . 'input/products.json'); // Get json from sample file

    $products = json_decode($rawData, true);

    function_insert_variation_products($products['products']);

    echo "INSERT TEST DONE";
}

function tool_option_page() {

    prefix_enqueue();

    $current_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";

    if (isset($_POST['process_clearSession'])) {
        session_destroy();
        echo '<br/><strong>THE SESSION HAS BEEN DELETED. GO CONTINUE YOUR JOB.</strong><br/>';
        exit;
    }
    echo '<div class="wrap">';

    echo '<div class="row">
                <div class="col-lg-8">
                <div class="panel panel-default">
    <div class="panel-heading">
       Google Merchant Manager Tools  
    </div>
    <div class="panel-body">
        <form method="POST" action="' . $current_link . '?page=delete-single-merchant-product">
            <p><button type="submit" class="btn btn-primary btn-lg">Delete Single Merchant Product</button></p>
        </form>
        <form method="POST" action="' . $current_link . '?page=delete-all-merchant-products">
            <p><button type="submit" class="btn btn-primary btn-lg">Delete All Merchant Products</button></p>
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
            <p><button type="submit" class="btn btn-primary btn-lg">Clear Session</button></p>
        </form>
    </div>
    </div>
    </div>
</div>';
}

?>