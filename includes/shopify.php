<?php

class Shopify {

    var $_storeURL = null;
    var $_apiKey = null;
    var $_secret = null;
    var $xml = null;

    function Shopify($storeURL, $apiKey, $secret) {
        $this->__construct($storeURL, $apiKey, $secret);
    }

    function __construct($storeURL, $apiKey, $secret) {
        $this->_storeURL = $storeURL;
        $this->_apiKey = $apiKey;
        $this->_secret = $secret;
    }

    /**
     * Retrieve an array of orders.
     *
     * @param array Associative array of filters
     * @returns        An array of order data
     */
    function initCallProducts($products, $start_page, $end_page) {
        
        $url = "https://" . "{$this->_apiKey}:{$this->_secret}@{$this->_storeURL}" .
                "/admin/products.json";

        // create cURLs resources
        for ($i = $start_page; $i <= $end_page; $i++) {
            
            $url = "https://" . "{$this->_apiKey}:{$this->_secret}@{$this->_storeURL}" .
                "/admin/products.json?limit=" . $products . '&page=' . $i;
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 500);
            curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 10); 
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

            $data = curl_exec($curl);   

//            echo $data;

            curl_close($curl);
        }

        return true;
    }
    
    /**
     * Retrieve an array of orders.
     *
     * @param array Associative array of filters
     * @returns        An array of order data
     */
    function getProducts($filters = array()) {
        $filter[] = "";
        foreach ($filters as $key => $value) {
            $filter[] = "{$key}=" . urlencode($value);
        }
        
        $url = "https://" . "{$this->_apiKey}:{$this->_secret}@{$this->_storeURL}" .
                "/admin/products.json" . (count($filter) ? '?' . join('&', $filter) : '');

//        return $url;
        
        $result = $this->makeCall($url);
        
        $result = json_decode($result, TRUE);
        
        
        if (json_last_error() == JSON_ERROR_NONE) {
            return $result;
        }
        return false;
    }
    
    function getCountProducts($filters = array()) {
        $filter[] = "";
        foreach ($filters as $key => $value) {
            $filter[] = "{$key}=" . urlencode($value);
        }
        $url = "https://" . "{$this->_apiKey}:{$this->_secret}@{$this->_storeURL}" .
                "/admin/products/count.json";

        $result = $this->makeCall($url);
        
        $result = json_decode($result, TRUE);
        
        if (isset($result['count'])) {
            return $result['count'];
        } 

        return false;
    }
    

    function makeCall($url) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            curl_setopt($$curl, CURLOPT_SSL_VERIFYPEER, false);
        }

        $result = curl_exec($curl);
        
        return $result;
    }

}

?>