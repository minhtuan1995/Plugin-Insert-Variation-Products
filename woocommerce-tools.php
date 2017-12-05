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
require_once('includes/helper.php');

add_action('plugins_loaded', 'woocommerce_tools_plugin_init');

function woocommerce_tools_plugin_init() {
    add_action('admin_menu', 'woocommerce_tools_admin_menu');
    add_action('login_init', 'send_frame_options_header', 10, 0);
    add_action('admin_init', 'send_frame_options_header', 10, 0);
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

function function_redirection_page() {
    
    load_assets_redirection();
    
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
        
        $string_add = "Added";
        if ( !add_post_meta($_POST['post_id'], '_redirection_url', $_POST['redirect_url'], TRUE) ) {
            $string_add = "Updated";
            update_post_meta($_POST['post_id'], '_redirection_url', $_POST['redirect_url']);
        }
        
        echo '<div class="alert alert-success">
                        <strong>' . $string_add . ' the redirection successful. Coupon ID: <font color="red">' . $_POST['post_id'] . '</font><br/>
                            URL: <font color="blue">' . $_POST['redirect_url'] . '</font>
                            </strong>
            </div>';
        
    }
    echo '<form role="form" method="post">
                                <div class="form-group">
                                    <label>Coupon ID</label>
                                    <input type="number" class="form-control" id="post_id" name="post_id" value="38179" required>
                                </div>
                                <div class="form-group">
                                    <label>Redirect URL</label>
                                    <input type="text" class="form-control" id="redirect_url" name="redirect_url" value="https://google.com.vn" required>
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
        
        $string_add = "Added";
        if ( !add_post_meta($_POST['post_id'], '_redirection_url', $_POST['redirect_url'], TRUE) ) {
            $string_add = "Updated";
            update_post_meta($_POST['post_id'], '_redirection_url', $_POST['redirect_url']);
        }
        
        echo '<div class="alert alert-success">
                        <strong>' . $string_add . ' the redirection successful. Coupon ID: <font color="red">' . $_POST['post_id'] . '</font><br/>
                            URL: <font color="blue">' . $_POST['redirect_url'] . '</font>
                            </strong>
            </div>';
        
    }
    
        echo '<form role="form" method="post">
                                <div class="form-group">
                                    <label>Store ID</label>
                                    <input type="number" class="form-control" id="post_id" name="post_id" value="38179" required>
                                </div>
                                <div class="form-group">
                                    <label>Redirect URL</label>
                                    <input type="text" class="form-control" id="redirect_url" name="redirect_url" value="https://google.com.vn" required>
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
                                <div class="row"><div class="col-sm-6">
                                    <div class="dataTables_length" id="dataTables-example_length">
                                        <label>Show 
                                            <select name="dataTables-example_length" aria-controls="dataTables-example" class="form-control input-sm">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> entries
                                        </label>
                                    </div>
                                </div>
                            <div class="col-sm-6"><div id="dataTables-example_filter" class="dataTables_filter"><label>Search:<input type="search" class="form-control input-sm" placeholder="" aria-controls="dataTables-example"></label></div></div></div><div class="row"><div class="col-sm-12"><table width="100%" class="table table-striped table-bordered table-hover dataTable no-footer dtr-inline" id="dataTables-example" role="grid" aria-describedby="dataTables-example_info" style="width: 100%;">
                               <thead>
                                <tr role="row">
                                   <th class="sorting_desc" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column ascending" style="width: 10px;" aria-sort="descending">Meta ID</th>
                                   <th class="sorting" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending" style="width: 10px;">Post ID</th>
                                   <th class="sorting" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Platform(s): activate to sort column ascending" style="width: 200px;">Post Title</th>
                                   <th class="sorting" tabindex="0" aria-controls="dataTables-example" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending" style="width: 300px;">Redirect URL</th>
                                </tr>
                             </thead>
                                <tbody>';
    
    $all_redirections = $dbModel->getAllRedirection();
//    echo "<pre>";
//    print_r($all_redirections);
//    echo "<pre>";
//    exit;
//    
    $count = 0;
    foreach ($all_redirections as $redirect) {
        $count++;
        if ($count % 2 == 0) {
            $row_color = "gradeA odd";
        } else {
            $row_color = "gradeA even";
        }
        echo ' <tr class="' . $row_color . '" role="row">
                        <td class="sorting_1">' . $redirect['meta_id'] . '</td>
                       <td class="center">' . $redirect['post_id'] . '</td>
                           <td><a href="' . $redirect['guid'] . '" target="_blank">' . $redirect['post_title'] . '</a></td>
                       <td>' . $redirect['meta_value'] . '</td>
                </tr>';
    }
                                
                    echo '</tbody>
                            </table></div></div>
                            <!-- <div class="row"><div class="col-sm-6"><div class="dataTables_info" id="dataTables-example_info" role="status" aria-live="polite">Showing 1 to 10 of 57 entries</div></div><div class="col-sm-6"><div class="dataTables_paginate paging_simple_numbers" id="dataTables-example_paginate"><ul class="pagination"><li class="paginate_button previous disabled" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_previous"><a href="#">Previous</a></li><li class="paginate_button active" aria-controls="dataTables-example" tabindex="0"><a href="#">1</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">2</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">3</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">4</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">5</a></li><li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">6</a></li><li class="paginate_button next" aria-controls="dataTables-example" tabindex="0" id="dataTables-example_next"><a href="#">Next</a></li></ul></div></div></div></div> --> 
                            <!-- /.table-responsive -->
                            
                        </div>
                        <!-- /.panel-body -->
                    </div>';
    
    echo '</div></div></div></div>';
}
?>