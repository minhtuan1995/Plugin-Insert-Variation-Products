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

}
