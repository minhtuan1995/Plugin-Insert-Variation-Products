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

if (!defined('DB_REDIRECTION')) {
    define('DB_REDIRECTION', 'wp_td_redirection');
}

require_once('autoload.php');
require_once('includes/helper.php');

add_action('plugins_loaded', 'woocommerce_tools_plugin_init');
register_activation_hook(__FILE__, 'redirection_create_db');

function woocommerce_tools_plugin_init() {
    add_action('admin_menu', 'woocommerce_tools_admin_menu');
    add_action('login_init', 'send_frame_options_header', 10, 0);
    add_action('admin_init', 'send_frame_options_header', 10, 0);
}


/*
 * Testing
 */

function function_insert_test_page() {
    echo get_term_link(34);
    //echo get_permalink('400');
}

function redirection_create_db() {
    global $wpdb;
    $db_name = DB_REDIRECTION;
    $charset_collate = $wpdb->get_charset_collate();
    
    // create the ECPT metabox database table
    if($wpdb->get_var("show tables like '$db_name'") != $db_name) 
    {
            $sql = 'CREATE TABLE ' . $db_name . ' (
            `re_id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `re_source` mediumint(9) NOT NULL,
            `re_source_multi` text NOT NULL,
            `re_destination` text NOT NULL,
            `re_type` tinytext NOT NULL,
            `re_active` bit NOT NULL,
            UNIQUE KEY re_id (re_id)
            )' . $charset_collate . ';
                
            CREATE INDEX idx_postid ON $db_name (re_source);';

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
    }
}

add_action( 'wp_enqueue_scripts', 'global_admin_ajax' );

function function_redirection_page() {
    
    load_assets_redirection();
    global_admin_ajax();
    
    $dbModel = new DbModel(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    echo '<div class="wrap">';
    echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong><font color="blue">COUPON Redirection</font></strong>
                        </div>
                        <div class="panel-body">';
                        
    if (isset($_POST['process_addNewCouponRedirection'])) {
        
        $add = $dbModel->add_redirection($_POST['post_id'], $_POST['post_redirect_url'], 'coupon');
        
        if ($add) {
            $string_add = "Added";
        } else {
            $string_add = "Updated";
        }
        
        echo '<div class="alert alert-success">
                        <strong>' . $string_add . ' the redirection successful. Coupon ID: <font color="red">' . $_POST['post_id'] . '</font><br/>
                            URL: <font color="blue">' . $_POST['post_redirect_url'] . '</font>
                            </strong>
            </div>';
        
    }
    echo '<form role="search" method="get">
                    <div class="form-group input-group">
                                            <input type="text" id="post_search" name="s" class="form-control search-autocomplete" placeholder="Search">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="button" disabled><i class="fa fa-search" button></i>
                                                </button>
                                            </span>
                                        </div>
            </form>';
    echo '<form role="form" method="post">
                                <div class="form-group">
                                    <label>Coupon ID</label>
                                    <input type="number" class="form-control" id="post_id" name="post_id" value="400" required>
                                </div>
                                <div class="form-group">
                                    <label>Redirect URL</label>
                                    <input type="text" class="form-control" id="post_redirect_url" name="post_redirect_url" value="https://google.com.vn" required>
                                </div>
                                
                                <input type="hidden" id="process_addNewCouponRedirection" name="process_addNewCouponRedirection">

                                <button type="submit" class="btn btn-success">Add New</button>
                                <button type="reset" class="btn btn-default">Reset</button>
        </form>';
    
    echo '</div></div></div>';
    
    echo '
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong><font color="blue">STORE Redirection</font></strong>
                        </div>
                        <div class="panel-body">';
    
    if (isset($_POST['process_addNewStoreRedirection'])) {
        
        $add = $dbModel->add_redirection($_POST['store_id'], $_POST['store_redirect_url'], 'store');
        
        if ($add) {
            $string_add = "Added";
        } else {
            $string_add = "Updated";
        }
        
        echo '<div class="alert alert-success">
                        <strong>' . $string_add . ' the redirection successful. Coupon ID: <font color="red">' . $_POST['store_id'] . '</font><br/>
                            URL: <font color="blue">' . $_POST['store_redirect_url'] . '</font>
                            </strong>
            </div>';
        
    }
        echo '<form role="search" method="get">
                    <div class="form-group input-group">
                                            <input type="text" id="store_search" name="s" class="form-control search-autocomplete" placeholder="Search">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="button" disabled><i class="fa fa-search" button></i>
                                                </button>
                                            </span>
                                        </div>
            </form>';
        echo '<form role="form" method="post">
                                <div class="form-group">
                                    <label>Store ID</label>
                                    <input type="number" class="form-control" id="store_id" name="store_id" value="34" required>
                                </div>
                                <div class="form-group">
                                    <label>Redirect URL</label>
                                    <input type="text" class="form-control" id="store_redirect_url" name="store_redirect_url" value="https://google.com.vn" required>
                                </div>
                                
                                <input type="hidden" id="process_addNewStoreRedirection" name="process_addNewStoreRedirection">

                                <button type="submit" class="btn btn-success">Add New</button>
                                <button type="reset" class="btn btn-default">Reset</button>
        </form>';
      
        echo '</div></div></div></div>';
        echo '<div class="row"> 
            <div class="col-lg-12">';
        echo '<div class="panel panel-default">
                        <div class="panel-heading">
                            Redirection List
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div id="dataTables-example_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
                                <div class="row">
                            
                            </div></div>
                            <div class="row">
                            <div class="col-sm-12">
                            <table width="100%" class="table table-striped table-bordered table-hover dataTable no-footer dtr-inline" id="dataTables-example" role="grid" aria-describedby="dataTables-example_info" style="width: 100%;">
                               <thead>
                                <tr role="row">
                                   <th class="sorting_desc" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending" style="width: 5px;" aria-sort="descending">ReID</th>
                                   <th class="sorting" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending" style="width: 5px;">PostID</th>
                                   <th class="sorting" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending" style="width: 5px;">Type</th>
                                   <th class="sorting" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Platform(s): activate to sort column ascending" style="width: 70px;">Title</th>
                                   <th class="sorting" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending" style="width: 100px;">Redirect URL</th>
                                   <th aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending" style="width: 30px;"></th>
                                </tr>
                             </thead>
                                <tbody>';
    
    $all_coupon_redirections = $dbModel->getAllCouponRedirection();
    $all_store_redirections = $dbModel->getAllStoreRedirection();
    
    $all_redirections = array_merge($all_coupon_redirections, $all_store_redirections);
    
    $count = 0;
    foreach ($all_redirections as $redirect) {

        if ($redirect['re_type'] == 'store') {
            $url = get_term_link((int)$redirect['re_source']);
        } else {
            $url = get_permalink($redirect['re_source']);
        }

        if (isset($url->errors)) {
            $url = '';
        }

        $count++;
        if ($count % 2 == 0) {
            $row_color = "gradeA odd";
        } else {
            $row_color = "gradeA even";
        }
        echo ' <tr class="' . $row_color . '" role="row" re_id = "' . $redirect['re_id'] . '">
                        <td class="sorting_1">' . $redirect['re_id'] . '</td>
                       <td class="center">' . $redirect['re_source'] . '</td>
                           <td class="center">' . $redirect['re_type'] . '</td>';
            if (!empty($url)) {
                echo '<td class="center"><a href="' . $url . '" target="_blank" >' . $redirect['name'] . ' </a></td>';
            } else {
                echo '<td class="center">' . $redirect['name'] . '</td>';
            }
                            
            echo '<td>' . urldecode($redirect['re_destination']) . '</td>';
                       
//            if ($redirect['re_active']) {
//                echo '<td><button id="re_status_' . $redirect['re_id'] . '" type="button" class="btn btn-success btn-xs" onclick="setInactive(' . $redirect['re_id'] . ')">ON</button></td>';
//            } else {
//                echo '<td><button id="re_status_' . $redirect['re_id'] . '" type="button" class="btn btn-default btn-xs" onclick="setActive(' . $redirect['re_id'] . ')">OFF</button></td>';
//            }
             if ($redirect['re_active']) {
                echo '<td><input class="redirection-active" type="checkbox" data-toggle="toggle" data-size="mini" data-on="Enabled" data-off="Disabled" checked>';
            } else {
                echo '<td><input class="redirection-active" type="checkbox" data-toggle="toggle" data-size="mini" data-on="Enabled" data-off="Disabled">';
            }
            
            echo '  <button type="button" class="btn btn-danger btn-xs button-delete" title="Delete this redirection"><i class="fa fa-times"></i></button>';
            echo '</td>';
        echo '</tr>';
    }
    // <td><a href="' . $redirect['guid'] . '" target="_blank">' . $redirect['post_title'] . '</a></td>                            
                    echo '</tbody>
                            </table></div></div>
                            <!-- <div class="row"><div class="col-sm-6"><div class="dataTables_info" id="dataTables-example_info" role="status" aria-live="polite">Showing 1 to 10 of 57 entries</div></div><div class="col-sm-6"><div class="dataTables_paginate paging_simple_numbers" id="dataTables-example_paginate"><ul class="pagination"><li class="paginate_button previous disabled" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_previous"><a href="#">Previous</a></li><li class="paginate_button active" aria-controls="dataTables-example" tabindex="0"><a href="#">1</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">2</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">3</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">4</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">5</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">6</a></li><li class="paginate_button next" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_next"><a href="#">Next</a></li></ul></div></div></div></div> --> 
                            <!-- /.table-responsive -->
                            
                        </div>
                        <!-- /.panel-body -->
                    </div>';
    
    echo '</div></div></div>';
}


function ja_ajax_search() {
    
	$results = new WP_Query( array(
		'post_type'     => array( 'post', 'coupon' ),
		'post_status'   => 'publish',
		'nopaging'      => true,
		'posts_per_page'=> 100,
		's'             => stripslashes( $_POST['search'] ),
	) );
        
	$items = array();
	if ( !empty( $results->posts ) ) {
		foreach ( $results->posts as $result ) {
                    
                    $item['ID'] = $result->ID;
                    $item['post_title'] = $result->post_title;
                    $items[] = $item;
//			$items[] = $result->post_title;
                        
		}
	}
	wp_send_json_success( $items );
        
}

add_action( 'wp_ajax_search_site',        'ja_ajax_search' );
add_action( 'wp_ajax_nopriv_search_site', 'ja_ajax_search' );

function ja_ajax_search_store() {
        
        $dbModel = new DbModel(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
	$results = $dbModel->getAllCouponStore();
        
	$items = array();
	if ( !empty($results) ) {
		foreach ( $results as $result ) {
                    $item['ID'] = $result['term_id'];
                    $item['post_title'] = $result['name'];
                    $items[] = $item;
		}
	}
	wp_send_json_success( $items );
        
}

add_action( 'wp_ajax_search_store',        'ja_ajax_search_store' );
add_action( 'wp_ajax_nopriv_search_store', 'ja_ajax_search_store' );

function ja_ajax_set_active_redirection() {
    
    $re_id = $_POST['id'];
    $re_active = $_POST['value'];
    
    $dbModel = new DbModel(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    $dbModel->update_active_redirection($re_id, $re_active);
    
    $return['status'] = 'ok';
    $return['re_id'] = $re_id;
    $return['re_active'] = $re_active;

    wp_send_json_success( $return );
}

add_action( 'wp_ajax_active_redirection', 'ja_ajax_set_active_redirection' );
add_action( 'wp_ajax_nopriv_active_redirection', 'ja_ajax_set_active_redirection' );

function ja_ajax_delete_redirection() {
    
    $re_id = $_POST['id'];
    
    $dbModel = new DbModel(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    $dbModel->delete_redirection($re_id);
    
    $return['status'] = 'ok';

    wp_send_json_success( $return );
}

add_action( 'wp_ajax_delete_redirection', 'ja_ajax_delete_redirection' );
add_action( 'wp_ajax_nopriv_delete_redirection', 'ja_ajax_delete_redirection' );
?>