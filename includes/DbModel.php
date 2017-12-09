<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DbModel
 *
 * @author MT
 */
class DbModel {

    private $link;

    public function __construct($host, $user, $pass, $dbname) {
        $this->link = mysqli_connect($host, $user, $pass, $dbname);
    }
    
    public function query($query) {
        $result = mysqli_query($this->link, $query);
        return $result;
    }


    public function getAllRedirection($type = '') {
        
        $query = "SELECT * FROM " . DB_REDIRECTION;
        
        if (!empty($type)) {
            $query .= 'WHERE re_type = "' . $type . '"';
        }
        
        $result = mysqli_query($this->link, $query);

        $return = mysqli_fetch_all($result, MYSQLI_ASSOC);

        return $return;
        
    }
    
    public function getAllStoreRedirection() {
        
        $query = "SELECT * FROM " . DB_REDIRECTION . ' INNER JOIN wp_terms ON re_source = wp_terms.term_id WHERE re_type = "store"';
        
        $result = mysqli_query($this->link, $query);

        if ($result) {
            $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $return = [];
        }

        return $return;
        
    }
    
    public function getAllCouponRedirection() {
        
        $query = "SELECT re_id, re_source, re_destination, re_type, re_active, post_title as 'name', re_count_non, re_count_redirect, re_count FROM " . DB_REDIRECTION . ' INNER JOIN wp_posts ON re_source = wp_posts.ID WHERE re_type = "coupon"';
        
        $result = mysqli_query($this->link, $query);

        if ($result) {
            $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $return = [];
        }

        return $return;
        
    }
    
    public function add_redirection($source, $destination, $type = 'post', $active = 1, $source_multi = '') {
        
        $exists = $this->check_exists_redirection($source, $type);
        
        if ($exists != false) {
            $this->update_redirection($exists['re_id'], $source, $destination, $type, $source_multi);
            return false;
        } else {
            $query = '  INSERT INTO ' . DB_REDIRECTION . '(re_source, re_source_multi, re_destination, re_type, re_active, re_count_non, re_count_redirect, re_count)
                        VALUES (
                        ' . $source . ',
                        "' . urlencode($source_multi) . '",
                        "' . urlencode($destination) . '",
                        "' . $type . '",'
                        . $active . 
                        ', 0, 0, 0)';
            $result = mysqli_query($this->link, $query);
            return true;
        }
    }
    
    public function check_exists_redirection($source, $type, $source_multi = '') {
        
        $query = 'SELECT * FROM ' . DB_REDIRECTION . ' WHERE re_source = ' . $source . ' AND re_type = "' . $type . '"';
        
        $result = mysqli_query($this->link, $query);

        $return = mysqli_fetch_assoc($result);

        if (empty($return)) {
            return false;
        }
        
        return $return;
    }

    public function update_redirection($re_id, $source, $destination, $type = 'post', $source_multi = '') {
        
        $query = '  UPDATE ' . DB_REDIRECTION . '
                    SET 
                    re_source = ' . $source . ',
                    re_source_multi = "' . urlencode($source_multi) . '",
                    re_destination = "' . urlencode($destination) . '",
                    re_type = "' . $type . '",
                    re_active = 1
                    WHERE re_id = ' . $re_id;

        $result = mysqli_query($this->link, $query);

        return $result;
        
    }
    
    public function update_redirection_part($re_id, $destination, $active) {
        
        $query = '  UPDATE ' . DB_REDIRECTION . '
                    SET 
                    re_destination = "' . urlencode($destination) . '",
                    re_active = ' . $active . '
                    WHERE re_id = ' . $re_id;

        $result = mysqli_query($this->link, $query);

        return $result;
        
    }

    public function update_active_redirection($re_id, $active = '1') {
        
        $query = '  UPDATE ' . DB_REDIRECTION . '
                    SET 
                    re_active = ' . $active . '
                    WHERE re_id = ' . $re_id;

        $result = mysqli_query($this->link, $query);

        return true;
        
    }
    
    public function delete_redirection($re_id) {
        
        $query = '  DELETE FROM ' . DB_REDIRECTION . ' WHERE re_id = '. $re_id;

        $result = mysqli_query($this->link, $query);

        return true;
        
    }
    
    public function update_count($re_id, $type_redirection = false) {
        
        if ($type_redirection) {
            $query = '  UPDATE ' . DB_REDIRECTION . '
                        SET 
                        re_count = re_count + 1,
                        re_count_redirect = re_count_redirect + 1
                        WHERE re_id = ' . $re_id;
        } else {
            $query = '  UPDATE ' . DB_REDIRECTION . '
                        SET 
                        re_count = re_count + 1,
                        re_count_non = re_count_non + 1
                        WHERE re_id = ' . $re_id;
        }
        
        $result = mysqli_query($this->link, $query);

        return $result;
        
    }
    
    public function getAllCouponStore($store_name = '') {
        
        $query = "SELECT wp_terms.term_id, wp_terms.name FROM wp_terms INNER JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id WHERE wp_term_taxonomy.taxonomy = 'coupon_store'";
        if (!empty($store_name)) {
            $query .= "AND wp_terms.name like '%" . $store_name . "%'";
        }
        $result = mysqli_query($this->link, $query);

        $return = mysqli_fetch_all($result, MYSQLI_ASSOC);

        return $return;
        
    }
}
