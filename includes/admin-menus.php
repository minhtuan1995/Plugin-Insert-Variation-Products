<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function woocommerce_tools_admin_menu() {

    add_menu_page('Woo Tools', 'Woo Tools', 'manage_options', 'variation-products-main', 'variation_products_main_page', 'dashicons-welcome-widgets-menus', 3);
    add_submenu_page('variation-products-main', __('Woo Converter'), __('Woo Converter'), 'manage_options', 'variation-products-main');
    add_submenu_page('variation-products-main', __('Woo Feed'), __('Woo Feed'), 'manage_options', 'function-woocommerce-feed', 'function_woocommerce_merchant_feed_page');
    add_submenu_page('variation-products-main', __('Shopify Feed'), __('Shopify Feed'), 'manage_options', 'function-shopify-feed', 'shopify_feed_main_page');
    
    add_submenu_page('variation-products-main', __('Test Function'), __('Test Function'), 'manage_options', 'function_insert_test_page', 'function_insert_test_page');
    add_submenu_page('variation-products-main', __('Redirection'), __('Redirection'), 'manage_options', 'function-redirection', 'function_redirection_page');
// Tools
    add_submenu_page('variation-products-main', __('Tool Options'), __('Tool Options'), 'manage_options', 'tool-options', 'tool_option_page');

    add_submenu_page('tool-options', __('Delete all merchant'), __('Delete all merchant'), 'manage_options', 'delete-all-merchant-products', 'delete_all_merchant_products');
    add_submenu_page('tool-options', __('Delete single merchant'), __('Delete single merchant'), 'manage_options', 'delete-single-merchant-product', 'delete_single_merchant_product');
// Add iframe to a sub-menu to hide
    add_submenu_page('tool-options', __('Iframe Insert Variations'), __('Iframe Insert Variations'), 'manage_options', 'iframe-insert-variations', 'iframe_insert_variations_page');
    add_submenu_page('tool-options', __('Iframe Feed Merchant'), __('Iframe Feed Merchant'), 'manage_options', 'iframe-feed-merchant', 'iframe_feed_merchant_page');
    add_submenu_page('tool-options', __('Iframe Shopify Feed Merchant'), __('Iframe Shopify Feed Merchant'), 'manage_options', 'iframe-shopify-feed-merchant', 'iframe_shopify_feed_page');

}