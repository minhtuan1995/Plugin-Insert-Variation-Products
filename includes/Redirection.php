<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Redirection
 *
 * @author dmtuan
 */
//if (!defined('REDIRECTION_SOURCE')) {
//    define('REDIRECTION_SOURCE', 'google.com');
//}

if (!defined('REDIRECTION_SOURCE')) {
    define('REDIRECTION_SOURCE', 'http://shoppingstore02.ga');
}

if (!class_exists( 'Redirection' ) ) {
    class Redirection {
        function __construct() {
            add_action('redirection_check', array(&$this, 'redirection_check_redirection'));
        }

        function redirection_check_redirection($post_id = '', $source = '') {
            
            if ($source == '') {
                $source = REDIRECTION_SOURCE;
            }
            
            if(isset($_SERVER['HTTP_REFERER'])) {
                
                $check = strpos($_SERVER['HTTP_REFERER'], REDIRECTION_SOURCE);
                
                if ($check !== false) {
                    
                    if (!empty($post_id)) {
                        $this->redirection_by_post_id($post_id);
                    }
                    
                }
            }
            
        }

        public function redirection_by_url($redirect_url = '') {
            if (!empty($redirect_url)) {
                echo '<meta http-equiv="refresh" content="0; url=' . $redirect_url . '">';
            }
        }
        
        public function redirection_by_post_id($post_id = '') {
            if (!empty($post_id)) {

                $redirect_url = get_post_meta($post_id, '_redirect_url', TRUE);

                if ($redirect_url) {
                    echo '<meta http-equiv="refresh" content="0; url=' . $redirect_url . '">';
                    exit;
                }
            }
        }
    }
}

if (class_exists( 'Redirection' )) {
    $MyRedirection = new Redirection();
}