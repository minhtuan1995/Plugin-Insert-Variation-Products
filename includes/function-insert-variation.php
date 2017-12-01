<?php

/*
 * MAIN FUNCTION - Create products
 */
function function_insert_variation_products($all_products, $countProduct = 0, $total_product) {
    
    foreach ($all_products as $single_product) {
        $countProduct++;
        // START - Process single product
        $t1 = microtime(true);

        $product_data = jsonConvert($single_product);
        echo 'Importing: <strong><font color="blue">' . $product_data['sku'] . '</font></strong> | ' . $product_data['name'] . '<br/>';
        
        //Update product if exists
        $productId = wc_get_product_id_by_sku($product_data['sku']);
        
        if ($productId != 0) {  // Update If exists
            $productExist = get_post($productId)->to_array();

            $single_product['updated_at'] = mysql2date('Y-m-d H:i:s', $single_product['updated_at']);

            if (strtotime($productExist['post_date_gmt']) == strtotime($single_product['updated_at'])) {
                echo '<font color="green"><strong>Product exist already...-> No Change From Source</strong></font><br/>';
            } else {
                echo '<font color="green"><strong>Product exist already...-> Updating </strong></font><br/>';
                update_product($productId, $product_data);
            }
        } ELSE {    // Create new product
            echo '<font color="blue"><strong>Create product...</strong></font><br/>';
            try {
                insert_product($product_data);
            } catch (Exception $ex) {
                echo "<pre>";
                print_r($ex);
                echo "<pre>";
            }
        }
        
        $t2 = microtime(true);
        $t3 = $t2 - $t1;
        echo "Done " . $countProduct . "/" . $total_product . " | ";
        echo " Time: " . number_format($t3, 3) . "s";
        echo "<br/> ==================== <br/>";

        if ($countProduct % 3 == 0) {
            ob_flush();
            flush();
            usleep(2);
        }
        if ($countProduct >= $total_product) {
            return $countProduct;
        }
        // END - Process single product
    }
    
    return $countProduct;
}

function insert_variation_prepare() {
    global $wpdb;
    wp_defer_term_counting( true );
    wp_defer_comment_counting( true );
    
    if (!defined('WP_IMPORTING')) {
        define( 'WP_IMPORTING', true );
    }
    
    $wpdb->query( 'SET autocommit = 0;' );
    
    if (ob_get_level() == 0) {
        ob_start();
    }
}

function insert_variation_finish() {
    global $wpdb;
    ob_end_flush();
    wp_defer_term_counting( false );
    wp_defer_comment_counting( false );
    $wpdb->query( 'COMMIT;' );
}


