<?php

namespace Services\Grab\Google;

use Services\Grab as Grab;
use Exception;

class GoogleComService implements Grab\IGrabService{
    
    private $baseUrl = 'https://www.google.de/search';
    private $pageUrl = '';

    private $usePaging = false;

    public function __construct($usePaging = false){
        $this->usePaging = $usePaging;
    }
    
    public function getUrl(){
        return $this->baseUrl;
    }

    public function parse($lookupElement){
         $this->pageUrl = $this->baseUrl .  '?q=' . urlencode($lookupElement->name . ' ' . str_replace('&nbsp;','',$lookupElement->streetAddress) . ', ' . $lookupElement->postCode);
         $this->parsePage($this->pageUrl, $lookupElement);
    }

    private function parsePage($url, $lookupElement){
        
        echo PHP_EOL . "<br/>...parsing $url" . PHP_EOL;
        try{
            echo "t0" . PHP_EOL;
            //$html = file_get_html($url);


            $ch = curl_init();

            $header = array("Connection: Keep-Alive", "User-Agent: Mozilla/5.0");
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec ($ch);

            curl_close ($ch);

            $html = str_get_html($response);

            echo "t1" . PHP_EOL;
            if($html === FALSE){
                throw new Exception('error occurred');
            }
        }catch(Exception $e){
            echo "<br/>failed to open page $url" . PHP_EOL;
            return;
        }
        finally{
            $lookupElement->parsedwithgoogle = true;
        }

        $quickResultBlock = $html
                ->find('td#rhs_block',0);

        if($nameElement = $quickResultBlock->find('div.g div._o0d div._B5d', 0)){
             $lookupElement->googlename = $nameElement->plaintext;
        }

        if($addressElement = $quickResultBlock->find('div.g div._o0d div._gF span._tA', 0)){
             $lookupElement->googleaddress = $addressElement->plaintext;
        }

        if($phoneElement = $quickResultBlock->find('div.g div._o0d div._gF span._tA', 1)){
             $lookupElement->googlephone = $phoneElement->plaintext;
        }

        if($websiteElement = $quickResultBlock->find('div.g div._o0d div._pIf div._IGf a.fl', 1)){
             $lookupElement->googlewebsite = $this->getStrippedWebsite(ltrim($websiteElement->href, " /url?"));
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
        if($parsedArr && isset($parsedArr['q']))
            $result = $parsedArr['q'];
        return $result;
    }
}