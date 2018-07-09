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
         $this->pageUrl = $this->baseUrl . '?q=' . $this->getQuerystring($lookupElement);
         $this->parsePage($this->pageUrl, $lookupElement);
    }

    public function parseWebsite($lookupElement){
        throw new Exception('Not implemented');
    }

    private function getQuerystring($lookupElement){
        return urlencode($lookupElement->name . ' ' . str_replace('&nbsp;','',$lookupElement->streetAddress) . ', ' . $lookupElement->postCode);
    }

    private function parsePage($url, $lookupElement){
        
        echo PHP_EOL . "<br />...parsing $url" . PHP_EOL;
        try{
            //$html = file_get_html($url);


            $ch = curl_init();

            $header = array("Connection: Keep-Alive", "User-Agent: Mozilla/5.0");
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	        curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds

            $response = curl_exec ($ch);

            curl_close ($ch);

            $html = str_get_html($response);

            if($html === FALSE){
                throw new Exception('error occurred');
            }
        }catch(Exception $e){
            echo PHP_EOL . "<br />failed to open page $url" . PHP_EOL;
            return;
        }
        finally{
            $lookupElement->googlequerystring = $this->getQuerystring($lookupElement);
            $lookupElement->parsedwithgoogle = true;// !empty($lookupElement->googlename);
        }

        $quickResultBlock = $html
                ->find('td#rhs_block',0);

        if($quickResultBlock != null){

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

            if($lookupElement->googlewebsite){
                $this->parseWebPage($lookupElement->googlewebsite,$lookupElement);
            }else{
                if($lookupElement->website){
                    $this->parseWebPage($lookupElement->website,$lookupElement);
                }
            }

        }

        $html = null;
        $quickResultBlock = null;
    }

    private function parseWebPage($url, $lookupElement){
        echo PHP_EOL . "<br />....WEBPAGE PARSING: $url" . PHP_EOL;
        try{
            $ch = curl_init();

            $header = array("Connection: Keep-Alive", "User-Agent: Mozilla/5.0");
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	        curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds

            $response = curl_exec ($ch);

            curl_close ($ch);

            $html = str_get_html($response);

            if($html === FALSE){
                throw new Exception('error occurred');
            }
        }catch(Exception $e){
            echo PHP_EOL . "<br />FAILED TO PARSE WEBPAGE: $url" . PHP_EOL;
            return;
        }

        if($descriptionElement = $html->find('meta[name=description]', 0)){
            if(mb_check_encoding($descriptionElement->content, "UTF-8")){
                $lookupElement->googlewebsitemetadescription = $descriptionElement->content;
            }else{
                echo PHP_EOL . "<br />!!!INVALID ENCODING(description): $descriptionElement->content" . PHP_EOL;
            }
        }

        if($keywordsElement = $html->find('meta[name=keywords]', 0)){
            if(mb_check_encoding($keywordsElement->content, "UTF-8")){
                $lookupElement->googlewebsitemetakeywords = $keywordsElement->content;
            }else{
                echo PHP_EOL . "<br />!!!INVALID ENCODING(keyword): $keywordsElement->content" . PHP_EOL;
            }
        }
        echo PHP_EOL . "<br />WEBPAGE PARSED";
        $html = null;
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