<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function prefix_enqueue() {       
    // JS
    wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    wp_enqueue_script('prefix_bootstrap');
    
    // CSS
    wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
    wp_enqueue_style('prefix_bootstrap');
    
    wp_enqueue_style('my-styles', WC_PLUGIN_URL . 'assets/styles.css' );
    
}

function prefix_enqueue_insert_variation() {       
    // JS
    wp_enqueue_script('my-styles', WC_PLUGIN_URL . 'assets/insert_variation_main.js' );
}

?>