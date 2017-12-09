<?php

function jsonConvert($product) {
    
    $main_attributes = array();
    
    foreach ($product['options'] as $option) {
            if ($option['name'] == 'Product Type') {
                $main_attributes[] = "product-type";
            } elseif (($option['name'] == 'Size')) {
                $main_attributes[] = "size";
            } elseif (($option['name'] == 'Color')) {
                $main_attributes[] = "color";
            }
        }

    // Images
    if (isset($product['image']['src'])) {
        $product['image']['src'] = preg_replace('/\?.*/', '', $product['image']['src']);
        $main_thumb_image = array(
            'id' => $product['image']['id'],
            'src' => $product['image']['src'],
            'width' => $product['image']['width'],
            'height' => $product['image']['height'],
            'position' => 0,
        );
    }
    
    $main_images = array();
    foreach ($product['images'] as $image) {
        $image['src'] = preg_replace('/\?.*/', '', $image['src']);
        $img_single = array(
            'id' => $image['id'],
            'src' => $image['src'],
            'width' => $image['width'],
            'height' => $image['height'],
            'position' => $image['position'],
        );
        $main_images[] = $img_single;
    }

// Variations
    $main_variations = array();
    foreach ($product['variants'] as $variant) {

        $attributes = [];
        if (isset($variant['option1'])) {
            $attributes['product-type'] = $variant['option1'];
        }
        if (isset($variant['option2'])) {
            $attributes['size'] = $variant['option2'];
        }
        if (isset($variant['option3'])) {
            $attributes['color'] = $variant['option3'];
        }

        $var_images = array();
        foreach ($product['images'] as $image) {
            if ($image['id'] == $variant['image_id']) {
                $image['src'] = preg_replace('/\?.*/', '', $image['src']);
                $var_img = array(
                    'id' => $image['id'],
                    'src' => $image['src'],
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'position' => 0
                );
                $var_images[] = $var_img;
            }
        }

        //exit;
        $vari = array(
            'price' => $variant['price'],
            'regular_price' => $variant['compare_at_price'],
            'sale_price' => $variant['price'],
            'updated_at' => mysql2date('Y-m-d H:i:s', $variant['updated_at']),
            'attributes' => $attributes,
            "taxable" => false,
            "requires_shipping" => true
        );
        
        if (!empty($var_images)) {
            $vari['image'] = $var_images;
        }
        
        if (isset($variant['sku']) && !empty($variant['sku'])) {
            $vari['sku'] = $variant['sku'];
        } else {
            $vari['sku'] = $variant['id'];
        }

        $main_variations[] = $vari;
    }

// Get list tags and convert to array
    $main_tags = explode(", ", $product['tags']);

// Get description 
    $description = str_replace("\n", '', $product['body_html']);
    
    if (isset($product['title'])) {
        
    }
    $main_product = array(
        'name' => $product['title'],
        'type' => 'variable',
        "in_stock" => true,
        "visible" => true,
        'description' => $description,
        'enable_html_description' => true,
        'updated_at' => mysql2date('Y-m-d H:i:s', $product['updated_at']),
        'images' => $main_images,
        'available_attributes' => $main_attributes,
        'variations' => $main_variations
    );
    
    if (isset($main_thumb_image)) {
        $main_product['image'] = $main_thumb_image;
    }
    
    if (isset($product['sku']) && !empty($product['sku'])) {
        $main_product['sku'] = $product['sku'];
    } elseif (isset($product['handle']) && !empty($product['handle'])) {
        $pos = strrpos($product['handle'], "-");
        if ($pos!=false) {
            $main_product['sku'] = substr($product['handle'], $pos+1);
        }
    }

    if (!empty($main_tags)) {
        $main_product['tags'] = $main_tags;
    }
    
    return $main_product;
}

?>