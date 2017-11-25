<?php 

function update_product($productId, $product_data)  
{
    $attachments = array();
    $post = array( // Set up the basic post data to insert for our product
        'ID'       => $productId,
        'post_author'  => 1,
        'post_content' => $product_data['description'],
        'post_status'  => 'publish',
        'post_title'   => $product_data['name'],
        'post_parent'  => '',
        'post_type'    => 'product',
        'post_date_gmt'    => mysql2date('Y-m-d H:i:s', $product_data['updated_at'])
    );

    $post_id = wp_update_post($post); // Insert the post returning the new post id
    
    if (!$post_id) // If there is no post id something has gone wrong so don't proceed
    {
        return false;
    }

    update_post_meta($post_id, '_sku', $product_data['sku']); // Set its SKU
    update_post_meta( $post_id,'_visibility','visible'); // Set the product to visible, if not it won't show on the front end

//    wp_set_object_terms($post_id, $product_data['categories'], 'product_cat'); // Set up its categories
    wp_set_object_terms($post_id, 'variable', 'product_type'); // Set it to a variable product type

    // Update image thumb for main product
    if (isset($product_data['image'])) 
    {
        // Check if attachment already => just get and update to product
        $attachId = wc_get_product_id_by_sku($product_data['image']['id']);
        if ($attachId != 0) {
            update_post_meta($post_id, '_thumbnail_id', $attachId);  
        } else {
            $attachment_id = attach_image($product_data['image'], '', $post_id);
            $attachments[$product_data['image']['id']] = $attachment_id;
        
            update_post_meta($post_id, '_thumbnail_id', $attachment_id);  
        }
    }
    
    foreach ($product_data['images'] as $image)
    {
        $attachId = wc_get_product_id_by_sku($image['id']);
        if ($attachId != 0) {
            $ids[] = $attachId;
            $attachments[$image['id']] = $attachId;
        } else {
            $attachment_id = attach_image($image, '', $post_id);
            $ids[] = $attachment_id;
            $attachments[$image['id']] = $attachment_id;
        }
    }
    delete_post_meta($post_id, '_product_image_gallery');
    update_post_meta($post_id, '_product_image_gallery', implode(',', $ids));

    update_product_attributes($post_id, $product_data['available_attributes'], $product_data['variations']); // Add attributes passing the new post id, attributes & variations
    update_product_variations($post_id, $product_data['variations'], $attachments); // Insert variations passing the new post id & variations   
    //Update tags
    wp_set_post_terms($post_id, $product_data['tags'], 'product_tag');
}

function update_product_attributes ($post_id, $available_attributes, $variations)  
{
    foreach ($available_attributes as $attribute) // Go through each attribute
    {   
        $values = array(); // Set up an array to store the current attributes values.

        foreach ($variations as $variation) // Loop each variation in the file
        {
            $attribute_keys = array_keys($variation['attributes']); // Get the keys for the current variations attributes

            foreach ($attribute_keys as $key) // Loop through each key
            {
                if ($key === $attribute) // If this attributes key is the top level attribute add the value to the $values array
                {
                    $values[] = $variation['attributes'][$key];
                }
            }
        }

        // Essentially we want to end up with something like this for each attribute:
        // $values would contain: array('small', 'medium', 'medium', 'large');

        $values = array_unique($values); // Filter out duplicate values
        
//        if ($attribute == 'size') {
//            $based = array('S','M','L','XL','XXL','3XL','4XL');
//            $based = array_flip($based);
//            $values = array_flip($values);
//
//            foreach ($values as $key => $value) {
//                if (isset($based[$key])) {
//                    $values[$key] = $based[$key];
//                }
//            }
//            asort($values);
//            $values = array_flip($values);
//        }
        
        // Store the values to the attribute on the new post, for example without variables:
        wp_set_object_terms($post_id, $values, 'pa_' . $attribute);
    }

    $product_attributes_data = array(); // Setup array to hold our product attributes data

    foreach ($available_attributes as $attribute) // Loop round each attribute
    {
        $product_attributes_data['pa_'.$attribute] = array( // Set this attributes array to a key to using the prefix 'pa'

            'name'         => 'pa_'.$attribute,
            'value'        => '',
            'is_visible'   => '1',
            'is_variation' => '1',
            'is_taxonomy'  => '1'

        );
    }

    update_post_meta($post_id, '_product_attributes', $product_attributes_data); // Attach the above array to the new posts meta data key '_product_attributes'
}

function update_product_variations ($post_id, $variations, $attachments)  
{
    foreach ($variations as $index => $variation)
    {
        $variationId = wc_get_product_id_by_sku($variation['sku']);
        if ($variationId == 0) {
            echo "Not found variation to update : <br/>";
            echo '<pre>';
            print_r($variation);
            echo '</pre>';
            continue;
        }
        $variation_post = array( // Setup the post data for the variation
            'ID'          => $variationId,
            'post_title'  => 'Variation #'.$index.' of '.count($variations).' for product#'. $post_id,
            'post_name'   => 'product-'.$post_id.'-variation-'.$index,
            'post_status' => 'publish',
            'post_parent' => $post_id,
            'post_type'   => 'product_variation',
            'guid'        => home_url() . '/?product_variation=product-' . $post_id . '-variation-' . $index,
            'post_date_gmt' => mysql2date('Y-m-d H:i:s', $variation['updated_at'])            
        );

        $variation_post_id = wp_update_post($variation_post); // Insert the variation

        foreach ($variation['attributes'] as $attribute => $value) // Loop through the variations attributes
        {   
            $attribute_term = get_term_by('name', $value, 'pa_'.$attribute); // We need to insert the slug not the name into the variation post meta

            update_post_meta($variation_post_id, 'attribute_pa_'.$attribute, $attribute_term->slug);
          // Again without variables: update_post_meta(25, 'attribute_pa_size', 'small')
        }
        
        update_post_meta($variation_post_id, '_sku', $variation['sku']); // Set its SKU
        update_post_meta($variation_post_id, '_price', $variation['price']);
        if (isset($variation['regular_price']) && !empty($variation['regular_price'])) {
            update_post_meta($variation_post_id, '_regular_price', $variation['regular_price']);
        }
        if (isset($variation['sale_price']) && !empty($variation['sale_price'])) {
            update_post_meta($variation_post_id, '_sale_price', $variation['sale_price']);
        }
        // Add image for variations
        if (isset($variation['image']) && !empty($variation['image'])) {
           if (isset($attachments[$variation['image'][0]['id']])) {
                add_post_meta($variation_post_id, '_thumbnail_id', $attachments[$variation['image'][0]['id']]);  
            } else {
                $attachId = wc_get_product_id_by_sku($product_data['image']['id']);
                if ($attachId != 0) {
                    update_post_meta($variation_post_id, '_thumbnail_id', $attachId);  
                } else {
                    $attachment_id = attach_image($variation['image'][0], '', $variation_post_id);
                    $attachments[$variation['image'][0]['id']] = $attachment_id;
                    update_post_meta($variation_post_id, '_thumbnail_id', $attachment_id);  
                }
            }
        }
    }
}

//function update_attach_image ($file, $filealt, $post_id)  
//{
//    $fileurl = $file['src'];
//    $filename = basename($fileurl); // Get the filename including extension from the $fileurl e.g. myimage.jpg
//
//    $source = $fileurl; // This is going to be where the image is located, depending on the fileurl you pass in this may not be needed
//    $destination = $source; // Specify where we wish to upload the file, generally in the wp uploads directory
//
//    $filetype = wp_check_filetype($destination); // Get the mime type of the file
//
//    $attachment = array( // Set up our images post data
//        'guid'           => $fileurl, 
//        'post_mime_type' => $filetype['type'],
//        'post_title'     => $filename,
//        'post_author'    => 1,
//        'post_content'   => ''
//    );
//
//    $attach_id = wp_insert_attachment( $attachment, $destination, $post_id ); // Attach/upload image to the specified post id, think of this as adding a new post.
//
////    $attach_data = wp_generate_attachment_metadata( $attach_id, $destination ); // Generate the necessary attachment data, filesize, height, width etc.
//    
//    $attach_data['width'] = $file['width'];
//    $attach_data['height'] = $file['height'];
//    
//    wp_update_attachment_metadata( $attach_id, $attach_data ); // Add the above meta data data to our new image post
//
//    if (!empty($filealt)) {
//        add_post_meta($attach_id, '_wp_attachment_image_alt', $filealt); // Add the alt text to our new image post
//    }
//    
//    update_post_meta($attach_id, '_sku', $file['id']);
//
//    return $attach_id; // Return the images id to use in the below functions
//}


?>