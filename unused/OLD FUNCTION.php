<?php

function function_export_to_xml($productPerFiles = MAX_PRODUCT_PER_FILE) {
    
        $wpUploadDir = wp_upload_dir();
        $UploadDir = $wpUploadDir['basedir'] . "/xml/";
        $UploadURL = $wpUploadDir['baseurl'] . "/xml/";
        
        if (!is_dir($UploadDir)) {
            wp_mkdir_p( $UploadDir );
            if ( wp_mkdir_p( $UploadDir ) === TRUE )
            {
                echo "Folder $UploadDir successfully created";
            }
            else
            {
                echo "We can't create the folder $UploadDir, Please help to create it manually and try again.";
                exit;
            }
        }
        
        $fileName = 'google_merchant_xml_' . date("d_m_Y_h_i_s") . '_' . rand(1000, 9000) . '.xml';
        $fileURL = $UploadDir . $fileName;
//        file_put_contents($fileURL, $xmlWriter->flush(true));
        
        $xmlWriter = new XMLWriter();
//        $xmlWriter->openMemory(); //<===
        $xmlWriter->openUri($fileURL);
        $xmlWriter->setIndent(true);
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('rss');
        $xmlWriter->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $xmlWriter->endAttribute();
        $xmlWriter->writeAttribute('version', '2.0');
        $xmlWriter->endAttribute();
        
        $xmlWriter->startElement('channel');

        $xmlWriter->startElement('title');
        $xmlWriter->writeCdata(get_bloginfo());
        $xmlWriter->endElement();
        
        $xmlWriter->writeElement('link', get_bloginfo('url'));

        $xmlWriter->startElement('description');
        $xmlWriter->writeCdata(get_bloginfo('description'));
        $xmlWriter->endElement();
        
        $args = array(
            'post_type' => 'product',
            'numberposts' => -1,
        );
        
        $startTime_getproduct = microtime(true);
        
        $products = get_posts( $args );
        $TotalProducts = count($products);
        
        $endTime_getproduct = microtime(true);
        $timediff = $endTime_getproduct - $startTime_getproduct;
        echo '#<strong><font color="blue"> Getting product Done: '. $TotalProducts .' in ' . number_format($timediff, 2) . 's</font></strong>';
        echo '</div></div></div></div>';
        ob_flush();
        flush();
        sleep(2);
        
        $countProducts = 0;
        $countVariations = 0;
        $saveFile = false;
        
        //Memory

            
        foreach($products as $product) {
            $countProducts++;
            if ($countProducts % $productPerFiles == 0 || $countProducts == $TotalProducts) {
                $saveFile = true;
            }
            $memXmlWriter = new XMLWriter();
            $memXmlWriter->openMemory();
            $memXmlWriter->setIndent(true);
        
            $memXmlWriter->startElement('item');

            $product_s = wc_get_product( $product->ID );

            $memXmlWriter->startElement('g:title');
            $memXmlWriter->writeCdata($product->post_title);
            $memXmlWriter->endElement();
            
            $memXmlWriter->writeElement('g:link', $product->guid);
            
            $memXmlWriter->startElement('g:description');
            $memXmlWriter->writeCdata(strip_tags($product->post_content));
            $memXmlWriter->endElement();
            
            if (!is_null($product_s->get_image_id())) {
                $image_url = wp_get_attachment_url($product_s->get_image_id());
                $memXmlWriter->writeElement('g:image_link', $image_url);
            }
            
            $memXmlWriter->writeElement('g:product_type', 'Apparel');
            $memXmlWriter->writeElement('g:google_product_category', 'Apparel & Accessories > Clothing');
            $memXmlWriter->writeElement('g:price', $product_s->get_price());
            $memXmlWriter->writeElement('g:gender', '');
            $memXmlWriter->writeElement('g:size', '');
            $memXmlWriter->writeElement('g:color', '');
            
            $memXmlWriter->writeElement('g:age_group', 'Adult');
            $memXmlWriter->writeElement('g:condition', 'new');
            $memXmlWriter->writeElement('g:id', $product_s->get_sku());
            $memXmlWriter->writeElement('g:identifier_exists', 'false');
            $memXmlWriter->writeElement('g:availability', 'in stock');
            
            $memXmlWriter->startElement('g:shipping');
            $memXmlWriter->writeElement('g:country', 'USA');
            $memXmlWriter->writeElement('g:service', 'Standard Shipping');
            $memXmlWriter->writeElement('g:price', '');
            $memXmlWriter->endElement();
            
            $memXmlWriter->startElement('g:tax');
            $memXmlWriter->writeElement('g:country', 'USA');
            $memXmlWriter->writeElement('g:region');
            $memXmlWriter->writeElement('g:rate');
            $memXmlWriter->writeElement('g:tax_ship', 'yes');            
            $memXmlWriter->endElement();
            
            if (!is_null($product_s->get_gallery_image_ids())) {
                foreach ($product_s->get_gallery_image_ids() as $image_id) {
                    $image_s_url = wp_get_attachment_url($image_id);
                    $memXmlWriter->writeElement('g:additional_image_link', $image_s_url);
                }
            }
            $countVariations++;
            // End Main product </item>
            $memXmlWriter->endElement();
            
            if ($product_s->get_type() == 'variable') {
                $args = array(
                    'post_parent' => $product->ID,
                    'post_type'   => 'product_variation',
                    'numberposts' => -1,
                );
                
                $variations = $product_s->get_available_variations();
                
                foreach ($variations as $variation) {
                    
                    $memXmlWriter->startElement('item');
//                    $vari_item = $xml->AppendNodeWithChild($channel, 'item');
                    
                    $memXmlWriter->writeElement('g:id', $variation['sku']);
                    $memXmlWriter->writeElement('g:title', $product_s->get_name());
                
                    $link = $product->guid;
                    if (isset($variation['attributes'])) {
                        $scount = 0;
                        foreach ($variation['attributes'] as $att => $key) {
                            if ($scount == 0) {
                                $link = $link . "?" . $att . "=" . $key;
                            } else {
                                $link = $link . "&" . $att . "=" . $key;
                            }
                            $scount++;
                        }
                    }
                    $memXmlWriter->writeElement('g:link', $link);
                    $memXmlWriter->startElement('g:description');
                    $memXmlWriter->writeCdata(strip_tags($product_s->get_description()));
                    $memXmlWriter->endElement();

                    if (isset($variation['image']['full_src'])) {
                        $memXmlWriter->writeElement('g:image_link', $variation['image']['full_src']);
                    }

                    $memXmlWriter->writeElement('g:product_type', 'Apparel');
                    $memXmlWriter->writeElement('g:google_product_category', 'Apparel & Accessories > Clothing');
                    $memXmlWriter->writeElement('g:price', $variation['display_regular_price'] . " USD");
                    if (isset($variation['display_price']) && !empty($variation['display_price'])) {
                        $memXmlWriter->writeElement('g:sale_price', $variation['display_price'] . " USD");    
                    }

                    $product_type = isset($variation['attributes']['attribute_pa_product-type']) ? $variation['attributes']['attribute_pa_product-type'] : '';

                    if ( !empty($product_type) && strpos($product_type, 'men') !== false) {
                        $memXmlWriter->writeElement('g:gender', 'male');
                    } elseif (( !empty($product_type) && strpos($product_type, 'women') !== false)) {
                        $memXmlWriter->writeElement('g:gender', 'female');
                    } else {
                        $memXmlWriter->writeElement('g:gender', 'unisex');
                    }

                    $product_size = isset($variation['attributes']['attribute_pa_size']) ? $variation['attributes']['attribute_pa_size'] : '';
                    if (!empty($product_size)) {
                        $memXmlWriter->writeElement('g:size', $product_size);
                    }

                    $product_color = isset($variation['attributes']['attribute_pa_color']) ? $variation['attributes']['attribute_pa_color'] : '';
                    if (!empty($product_color)) {
                        $memXmlWriter->writeElement('g:color', $product_color);
                    }

                    $memXmlWriter->writeElement('g:age_group', 'Adult');
                    $memXmlWriter->writeElement('g:condition', 'new');
                    $memXmlWriter->writeElement('g:identifier_exists', 'true');
                    $memXmlWriter->writeElement('g:availability', 'in stock');
                    
                    $memXmlWriter->startElement('g:shipping');
                    $memXmlWriter->writeElement('g:country', 'USA');
                    $memXmlWriter->writeElement('g:service', 'Standard Shipping');
                    $memXmlWriter->writeElement('g:price', '');
                    $memXmlWriter->endElement();
                    
                    $memXmlWriter->startElement('g:tax');
                    $memXmlWriter->writeElement('g:country', 'USA');
                    $memXmlWriter->writeElement('g:region');
                    $memXmlWriter->writeElement('g:rate');
                    $memXmlWriter->writeElement('g:tax_ship', 'yes');  
                    $memXmlWriter->endElement();
                    
                    $memXmlWriter->writeElement('g:item_group_id', $product_s->get_sku());
                    
                    // End variation item </item>
                    $memXmlWriter->endElement();
                    $countVariations++;
                    
                    if ($countVariations % BATCH_SIZE == 0) {
                        $batchXmlString = $memXmlWriter->outputMemory(true);
                        $xmlWriter->writeRaw($batchXmlString);
                        
                        $memXmlWriter->flush();
                        unset($memXmlWriter);
                        
                        $memXmlWriter = new XMLWriter();
                        $memXmlWriter->openMemory();
                        $memXmlWriter->setIndent(true);
                    }
                }
            }
            
//            echo $countProducts . " | " . $TotalProducts . " | " . ($countProducts == $TotalProducts) .  "<br/>";
            
            
            if ($saveFile == true) {
                $saveFile = false;
                
                // Make sure we write everything
                $batchXmlString = $memXmlWriter->outputMemory(true);
                $xmlWriter->writeRaw($batchXmlString);
                $memXmlWriter->flush();
                unset($memXmlWriter);
                
                $xmlWriter->endElement();
                $xmlWriter->endElement();
                $xmlWriter->endDocument();  

                echo '#<strong><font color="blue"> Export done file: ' . $UploadURL . $fileName . '</font></strong><br/>';
                ob_flush();
                flush();
                sleep(2);
                
                if ($TotalProducts == $countProducts) {
                    echo '#<strong><font color="blue"> ALL EXPORT DONE: ' . $TotalProducts . ' products.</font></strong><br/>';
                    break;
                }
                
                $fileName = 'google_merchant_xml_' . date("d_m_Y_h_i_s") . '_' . rand(1000, 9000) . '.xml';
                $fileURL = $UploadDir . $fileName;
                
                $xmlWriter = new XMLWriter();
                $xmlWriter->openUri($fileURL);
                $xmlWriter->setIndent(true);
                $xmlWriter->startDocument('1.0', 'UTF-8');
//                exit;
                $xmlWriter->startElement('rss');
                $xmlWriter->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
                $xmlWriter->endAttribute();
                $xmlWriter->writeAttribute('version', '2.0');
                $xmlWriter->endAttribute();

                $xmlWriter->startElement('channel');

                $xmlWriter->startElement('title');
                $xmlWriter->writeCdata(get_bloginfo());
                $xmlWriter->endElement();

                $xmlWriter->writeElement('link', get_bloginfo('url'));

                $xmlWriter->startElement('description');
                $xmlWriter->writeCdata(get_bloginfo('description'));
                $xmlWriter->endElement();
                
                $memXmlWriter = new XMLWriter();
                $memXmlWriter->openMemory();
                $memXmlWriter->setIndent(true);
            }
            
        }
        
//        $xmlWriter->endElement();
//        $xmlWriter->endElement();
//        
//        $fileName = 'google_merchant_xml_' . date("d_m_Y_h_i_s") . '_' . rand(1000, 9000) . '.xml';
//        $fileURL = $UploadURL . $fileName;
//        file_put_contents($fileURL, $xmlWriter->flush(true));
//        
//        echo '#<strong><font color="blue"> Export done file: ' . $fileName . '</font></strong><br/>';
//        echo "DONE";
}

function function_export_xml_page() {
    
    if (ob_get_level() == 0) ob_start();
    prefix_enqueue();
    
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    echo '<h3 class="page-header">EXPORT TO MARCHANT XML</h3>';
    echo '<div class="wrap">';

    if (isset($_POST['process_exportXML'] )) {
        prefix_enqueue();
        
        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Exporting Products to XML...
                        </div>
                        <div class="panel-body">';
        
            // MAIN PROCESS 
        if( ! filter_var($_POST['xml_number_product'], FILTER_VALIDATE_INT) ){

            $productPerFiles = MAX_PRODUCT_PER_FILE;
            echo '<div class="alert alert-success">
                    <strong>The input you entered is not valid, we get it default by: ' . MAX_PRODUCT_PER_FILE . '.</strong>
                </div>';
            echo '</div></div></div></div>';
            
        } else {
            $productPerFiles = $_POST['xml_number_product'];
        }
        
        // Exporting products
        echo '#<strong><font color="blue"> EXPORTING PRODUCTS...</font></strong>';
        
        ob_flush();
        flush();
        sleep(2);
        
        $starttime = microtime(true);
        
        function_export_to_xml($productPerFiles);
        
        $endtime = microtime(true);
        $timediff = $endtime - $starttime;
        
        echo '#<strong><font color="red"> DONE in ' . number_format($timediff, 2) . '.</font></strong>';
        
        echo '</div></div></div></div>';
        
        ob_flush();
        flush();
        sleep(2);
        
    } else {
        echo '<div class="row">
                <div class="col-lg-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Getting Products from Shopify...
                        </div>
                        <div class="panel-body">';
     
//            echo '<div class="alert alert-success">
//                        <strong>Connect to Shopify successful. Your total products: <font color="red">' . $checkConnection . '</font></strong>
//                </div>';
                   echo '<div class="row">
                        <div class="col-lg-12">
                            <form role="form" method="post">

                                    <div class="form-group">
                                    <label>Number Products Per File</label>
                                    <input class="form-control" id="xml_number_product" name="xml_number_product" value="1">
                                </div>

                                <input type="hidden" id="process_exportXML" name="process_exportXML">

                                <button type="submit" class="btn btn-default">Export to XML</button>
                                <button type="reset" class="btn btn-default">Reset</button>
                            </form>
                        </div>
                    </div>';
                    echo '</div></div></div></div>';
    }
    
    ob_end_flush();
    session_write_close(); 
}


?>