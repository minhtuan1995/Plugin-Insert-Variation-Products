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

require_once('autoload.php');

add_action('plugins_loaded', 'woocommerce_tools_plugin_init');

function woocommerce_tools_plugin_init() {
    add_action('admin_menu', 'woocommerce_tools_admin_menu');
    add_action('login_init', 'send_frame_options_header', 10, 0);
    add_action('admin_init', 'send_frame_options_header', 10, 0);
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

?>