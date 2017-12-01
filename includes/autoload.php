<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!defined('MAX_PRODUCT_BATCH')) {
    define('MAX_PRODUCT_BATCH', 250); 
}

define('MAX_PRODUCT_PAGE', 100);     // Number of product when process all products
//define('MAX_PRODUCT_PAGE', 250);     // Number of product when process all products
//define('BATCH_SIZE', 25);

require_once( WC_PLUGIN_DIR . 'google-api/vendor/autoload.php' );
//require_once('../google-api/vendor/autoload.php' );

require_once('function-merchant-woocommerce.php');
require_once('function-merchant-shopify.php');
require_once('function-insert-variation.php');
require_once 'function-insert.php';
require_once 'function-update.php';
require_once 'function-tools.php';
require_once 'jsonConvert.php';
require_once 'shopify.php';
require_once 'helper.php';
require_once('WooProducts.php');

