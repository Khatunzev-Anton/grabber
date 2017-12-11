<?php

namespace Services\Grab\Google;

use Services\Grab as Grab;
use Exception;

class GoogleComService implements Grab\IGrabService{
    
    private $baseUrl = 'https://www.google.com';
    private $pageUrl = '';

    private $usePaging = false;

    public function __construct($usePaging = false){
        $this->usePaging = $usePaging;
    }
    
    public function getUrl(){
        return $this->baseUrl;
    }

    public function parse($lookupElement){
         $PARSED_BLOCK_COUNT = 15;
         $this->pageUrl = $this->baseUrl .  '?q=' + rawurlencode($lookupElement->name . ' ' . $lookupElement->streetAddress . ', ' . $lookupElement->postCode);
         $this->parsePage($this->pageUrl, $lookupElement);
    }

    private function parsePage($url, $lookupElement){
        
        echo "<br/>...parsing $url";
        try{
            $html = @file_get_html($url);
            if($html === FALSE){
                throw new Exception('error occurred');
            }
        }catch(Exception $e){
            echo "<br/>failed to open page $url";
            return;
        }
        finally{
            $lookupElement->parsedwithgoogle = true;
        }

        $quickResultBlock = $html
                ->find('div#rhs>div#rhs_block div._OKe');

        if($nameElement = $quickResultBlock->find('div.kp-header div._b1m div.mod div.rhs_title span', 0)){
             $lookupElement->googlename = $nameElement->plaintext;
        }

        if($addressElement = $quickResultBlock->find('div._G1d div._b1m div.kno-fb-ctx span._Xbe', 0)){
             $lookupElement->googleaddress = $addressElement->plaintext;
        }

        if($phoneElement = $quickResultBlock->find('div._G1d div.mod div.kno-fb-ctx span._Xbe', 0)){
             $lookupElement->googlephone = $phoneElement->plaintext;
        }

        if($websiteElement = $quickResultBlock->find('div.kp-header div._b1m div._fdf div._Q1n div._mdf div._ldf a.ab_button', 0)){
             $lookupElement->googlewebsite = $this->getStrippedWebsite($websiteElement->href);
        }

        $html = null;
        $quickResultBlock = null;
    }

    private function getStrippedWebsite($str){
        $result = '';
        if(empty($str)){
            return $result;
        }
        parse_str($str,$parsedArr);
        if($parsedArr && isset($parsedArr['url']))
            $result = $parsedArr['url'];
        return $result;
    }
}