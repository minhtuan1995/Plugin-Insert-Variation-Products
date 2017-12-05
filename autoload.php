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
require_once('includes/DbModel.php');
require_once('includes/TD_Redirection.php');
require_once('includes/function-merchant-woocommerce.php');
require_once('includes/function-merchant-shopify.php');
require_once('includes/function-insert-variation.php');
require_once 'includes/function-insert.php';
require_once 'includes/function-update.php';
require_once 'includes/function-tools.php';
require_once 'includes/jsonConvert.php';
require_once 'includes/shopify.php';
require_once 'includes/helper.php';
require_once('includes/WooProducts.php');
require_once('includes/admin-menus.php');
