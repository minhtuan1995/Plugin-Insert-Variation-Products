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
    
    public function getAllRedirection() {
        
        $query = "SELECT meta_id, post_id, meta_value, post_title, guid FROM wp_postmeta INNER JOIN wp_posts ON wp_postmeta.post_id = wp_posts.ID WHERE meta_key = '_redirection_url'";

        $result = mysqli_query($this->link, $query);

        $return = mysqli_fetch_all($result, MYSQLI_ASSOC);

        return $return;
        
    }
    
    public function add_redirection($source, $destination, $type = 'post', $source_multi = '') {
        
        $query = '  INSERT INTO ' . DB_REDIRECTION . '(re_source, re_source_multi, re_destination, re_type, re_active)
                    VALUES (
                    ' . $source . ',
                    ' . $source_multi . ',
                    ' . $destination . ',
                    ' . $type . ',
                    1,
                )';

        $result = mysqli_query($this->link, $query);

        return true;
        
    }
    
    public function update_redirection($re_id, $source, $destination, $type = 'post', $source_multi = '') {
        
        $query = '  UPDATE ' . DB_REDIRECTION . '
                    SET 
                    re_source = ' . $source . ',
                    re_source_multi = ' . $source_multi . ',
                    re_destination = ' . $destination . ',
                    re_type = ' . $type . ',
                    re_active = 1
                    WHERE re_id = ' . $re_id;

        $result = mysqli_query($this->link, $query);

        return true;
        
    }
    
    public function delete_redirection($re_id) {
        
        $query = '  DELETE FROM ' . DB_REDIRECTION . ' WHERE re_id = '. $re_id;

        $result = mysqli_query($this->link, $query);

        return true;
        
    }
}
