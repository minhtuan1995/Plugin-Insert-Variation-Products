<?php

/*
 * MAIN FUNCTION - Create products
 */
function function_insert_variation_products($all_products, $page_products = 0, $page_end = 0) {
    
    $CountProcessed = 0;
    $CountAll = count($all_products);
    
    echo "<br/> ####################### <br/>";
    if ($page_products != 0) {
        echo '#<strong><font color="red"> START IMPORT PART '. $page_products . '/' . $page_end . ': From product ' . MAX_PRODUCT_PAGE*($page_products-1) . ' to ' . MAX_PRODUCT_PAGE*$page_products . '</font></strong>';
    } else {
        echo '#<strong><font color="red"> START IMPORTING...</font></strong>';
    }
    echo "<br/> ####################### <br/>";
    
    echo " => Getting " . $CountAll . " products.";
    echo "<br/> ==================== <br/>";

    ob_flush();
    flush();
    usleep(2);
    
    $starttime = microtime(true);
    
    foreach ($all_products as $single_product) {
        // START - Process single product
        $t1 = microtime(true);

        $product_data = jsonConvert($single_product);
        echo 'Importing: ' . $product_data['name'] . '<br/>';
        
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
        $CountProcessed++;
        $t2 = microtime(true);
        $t3 = $t2 - $t1;
        echo "Done " . $CountProcessed . "/" . $CountAll . " | ";
        echo " Time: " . number_format($t3, 3) . "s";
        echo "<br/> ==================== <br/>";

        if ($CountProcessed % 3 == 0) {
            ob_flush();
            flush();
            usleep(2);
        }
        // END - Process single product
    }

    $endtime = microtime(true);
    $timediff = $endtime - $starttime;
    echo "<br/> All time: <strong>" . number_format($timediff, 3) . "s</strong>";
    
    if ($page_products != 0) {
        echo '<br/> <font color="green">IMPORT PART ' . $page_products . '/' . $page_end . ' PROCESSING: <strong>PAGE DONE</strong></font><br/>';
        
    } else {
        echo '<br/> <font color="green">IMPORT PROCESSING: <strong>DONE</strong></font><br/>';
    }
    
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


