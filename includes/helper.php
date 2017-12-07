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
    
//    wp_register_script('jquery_validate', '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js');
//    wp_enqueue_script('jquery_validate');
    
    wp_enqueue_script('my-styles', WC_PLUGIN_URL . 'assets/insert_variation_main.js' );
    
    // CSS
    wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
    wp_enqueue_style('prefix_bootstrap');
    
    wp_enqueue_style('my-styles', WC_PLUGIN_URL . 'assets/styles.css' );
    
    
}

function load_assets_redirection() {
        // JS
       
    wp_register_script('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    wp_enqueue_script('prefix_bootstrap');
    wp_register_script('prefix_jquery', WC_PLUGIN_URL . 'assets/jquery-3.2.1.min.js');
    wp_enqueue_script('prefix_jquery'); 
    wp_register_script('prefix_datatable', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js');
    wp_enqueue_script('prefix_datatable');
    wp_register_script('prefix_datatable', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js');
    wp_enqueue_script('prefix_datatable');
    wp_register_script('prefix_toggle', WC_PLUGIN_URL . 'assets/bootstrap-toggle.js');
    wp_enqueue_script('prefix_toggle');
    
    wp_enqueue_script('my-scripts', WC_PLUGIN_URL . 'assets/redirection.js' );
//    
    // CSS
    wp_register_style('prefix_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
    wp_enqueue_style('prefix_bootstrap');
    wp_register_style('prefix_datatable', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css');
    wp_enqueue_style('prefix_datatable');
    wp_register_style('prefix_toggle', '//gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css');
    wp_enqueue_style('prefix_toggle');

    wp_enqueue_style('my-styles', WC_PLUGIN_URL . 'assets/styles.css' );
    wp_enqueue_style('font-awesome', WC_PLUGIN_URL . 'assets/font-awesome/css/font-awesome.min.css' );
}

function global_admin_ajax() {
    wp_enqueue_style(
            'jquery-auto-complete',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.css',
            array(),
            '1.0.7'
    );
    wp_enqueue_script(
            'jquery-auto-complete',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.js',
            array( 'jquery' ),
            '1.0.7',
            true
    );
    
    wp_enqueue_script(
		'global',
		WC_PLUGIN_URL . 'assets/redirection.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);
    
    wp_localize_script(
		'global',
		'global',
		array(
			'ajax' => admin_url( 'admin-ajax.php' ),
		)
	);
}

function prefix_enqueue_insert_variation() {       
    // JS
    wp_enqueue_script('my-styles', WC_PLUGIN_URL . 'assets/insert_variation_main.js' );
}

function create_iframe($iframe_function, $header, $task = '') {
    
    if (!empty($task)) {
        $header = '<strong>' . $header  . ': </strong>' . $task;
    } else {
        $header = '<strong>' . $header  . '</strong>';
    }
    
    echo '<div class="row">
            <div class="col-lg-12">
               <div class="panel panel-default">
                  <div class="panel-heading">' . $header . '</div>
                  <div class="panel-body">
                     <div class="row">
                           <div style="overflow: hidden; margin-left: 15px; margin-bottom: 15px; max-width: 150%;">
                              <iframe id="iframe-mt" class="iframe-mt" scrolling="yes" src="admin.php?page='. $iframe_function .'" style="border: 0px none; margin-left: -160px; height: 500px; margin-top: -50px; margin-bottom: -50px; width: 1180px;"> 
                              </iframe>
                           </div>
                           <button id="iframe_sroll_up" name="iframe_sroll_up" class="btn btn-default" style="margin-left: 20px;">Sroll Up</button>
                           <button id="iframe_sroll_down" name="iframe_sroll_down" class="btn btn btn-info" style="margin-left: 20px;">Sroll Down</button>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>';
}
?>