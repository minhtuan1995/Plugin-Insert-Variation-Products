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
}
