<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('ATTRIBUTE_TYPE', 'attribute_pa_product-type');
define('ATTRIBUTE_SIZE', 'attribute_pa_size');
define('ATTRIBUTE_COLOR', 'attribute_pa_color');

class WooProducts {

    private $link;

    public function __construct($host, $user, $pass, $dbname) {
        $this->link = mysqli_connect($host, $user, $pass, $dbname);
    }

    public function getProductMain($product) {
        if (!isset($product['ID'])) {
            return false;
        }

        $_product = array();
        $_product['ID'] = $product['ID'];
        $_product['post_title'] = $product['post_title'];
        $_product['description'] = $product['post_content'];
        $_product['link'] = $product['guid'];

        $product_metas = $this->getMetaPost($product['ID']);

        foreach ($product_metas as $key => $meta) {
            switch ($meta['meta_key']) {
                case '_sku':
                    $_product['sku'] = $meta['meta_value'];
                    break;
                case '_thumbnail_id':
                    $_product['image_link'] = $this->getThumbImageLink($meta['meta_value']);
                    break;
                case '_product_image_gallery':
                    $images = explode(",", $meta['meta_value']);
                    $countImages = 0;
                    if (is_array($images)) {
                        foreach ($images as $image) {
                            $countImages++;
                            if ($countImages > 10) {
                                break;
                            }
                            $_product['additional_image_link'][] = $this->getThumbImageLink($image);
                        }
                    }
                    break;
            }
        }

        return $_product;
    }

    public function getAllVariations($product) {

        if (!isset($product['ID'])) {
            return false;
        }

        //product_variation
        $query = "SELECT ID FROM wp_posts WHERE post_parent = {$product['ID']} and post_type = 'product_variation' limit 1";

        $result = mysqli_query($this->link, $query);

//        $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $return = mysqli_fetch_assoc($result);

        $product = $this->getProductMain($product);

        $variations = array();

        if (count($return) > 0) {
            $variation = $return;
//            foreach ($return as $key => $variation) {
                $variation['post_title'] = $product['post_title'];
                $variation['description'] = $product['description'];
                $variation['link'] = $product['link'];
                if (isset($product['sku'])) {
                    $variation['item_group_id'] = $product['sku'];
                }

                $variation_metas = $this->getMetaPost($variation['ID']);

                foreach ($variation_metas as $key => $meta) {
                    switch ($meta['meta_key']) {
                        case ATTRIBUTE_TYPE:
                            $variation['link'] .= "?" . ATTRIBUTE_TYPE . "={$meta['meta_value']}";
                            if (strpos($meta['meta_value'], 'men')) {
                                $variation['gender'] = 'male';
                            } elseif (strpos($meta['meta_value'], 'women')) {
                                $variation['gender'] = 'female';
                            } else {
                                $variation['gender'] = 'unisex';
                            }
                            break;
                        case ATTRIBUTE_SIZE:
                            $variation['link'] .= "&" . ATTRIBUTE_SIZE . "={$meta['meta_value']}";
                            $variation['size'] = $meta['meta_value'];
                            break;
                        case ATTRIBUTE_COLOR:
                            $variation['link'] .= "&" . ATTRIBUTE_COLOR . "={$meta['meta_value']}";
                            $variation['color'] = $meta['meta_value'];
                            break;
                        case '_sku':
                            $variation['sku'] = $meta['meta_value'];
                            break;
                        case '_price':
                            $variation['price'] = $meta['meta_value'];
                            break;
                        case '_regular_price':
                            $variation['regular_price'] = $meta['meta_value'];
                            break;
                        case '_sale_price':
                            $variation['sale_price'] = $meta['meta_value'];
                            break;
                        case '_thumbnail_id':
                            $variation['image_link'] = $this->getThumbImageLink($meta['meta_value']);
                            break;
                    }
                }
                
                if (!isset($variation['image_link'])) {
                    $variation['image_link'] = $product['image_link'];
                }
                // Save variation
                $variations[] = $variation;
//            }

            $product['color'] = $variations[0]['color'];
            $product['size'] = $variations[0]['size'];
            $product['gender'] = 'unisex';
            $product['price'] = $variations[0]['price'];
            if (isset($variations[0]['sale_price'])) {
                $product['sale_price'] = $variations[0]['sale_price'];
            }
            if (isset($variations[0]['regular_price'])) {
                $product['regular_price'] = $variations[0]['regular_price'];
            }
            
//            array_unshift($variations, $product);
//            $variations[] = $product;
        }

        return $product;
//        return $variations;
    }

    public function getAllProducts() {
        $query = "SELECT ID, post_content, post_title, guid, post_type FROM wp_posts WHERE post_type = 'product'";

        $result = mysqli_query($this->link, $query);

        $return = mysqli_fetch_all($result, MYSQLI_ASSOC);

        return $return;
    }

    private function getMetaPost($postID) {
        $query = "  select meta_key, meta_value
                from wp_postmeta
                where post_id = $postID";

        $result = mysqli_query($this->link, $query);

        $return = mysqli_fetch_all($result, MYSQLI_ASSOC);

        return $return;
    }

    private function getThumbImageLink($postID) {
        $query = "  select *
                from wp_posts
                where ID = $postID";

        $result = mysqli_query($this->link, $query);

        $return = mysqli_fetch_assoc($result);

        if (isset($return['guid'])) {
            return $return['guid'];
        }

        return false;
    }

}
