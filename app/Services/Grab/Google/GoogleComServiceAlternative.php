<?php

namespace Services\Grab\Google;

use Services\Grab as Grab;
use Exception;

class GoogleComServiceAlternative implements Grab\IGrabService{
    
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
         $lookupElement->parsedwithgooglealternative = true;
         $getQSMethodNames = ['getQuerystring' => 'googlequerystring','getQuerystring1' => 'googlequerystring1','getQuerystring2' => 'googlequerystring2'];

         foreach($getQSMethodNames as $qsMethodName=>$qsFieldName){
            $queryString = $this->$qsMethodName($lookupElement);
            try{
                $this->pageUrl = $this->baseUrl . '?q=' . $queryString;
                $html = $this->getHtml($this->pageUrl);
                $this->parsePage($html, $lookupElement);
                if(empty($lookupElement->googlename) || empty($lookupElement->googlewebsite)){
                    $this->parsePageAlternative($html, $lookupElement);
                }
                if(!empty($lookupElement->googlename)){
                    break;
                }
            }catch(Exception $e){
                echo PHP_EOL . "<br />EXCEPTION:" . $e->getMessage() . PHP_EOL;
            }finally{
                $lookupElement->$qsFieldName = $queryString;
            }
         }
    }

    public function parseWebsite($lookupElement){
        throw new Exception('Not implemented');
    }


    private function getQuerystring($lookupElement){
        return urlencode('Rechtanwalt ' . $lookupElement->name . ' ' . str_replace('&nbsp;','',$lookupElement->streetAddress) . ' ' . $lookupElement->postCode);
    }

    private function getQuerystring1($lookupElement){
        return urlencode('Rechtanwalt ' . $lookupElement->name . ' ' . str_replace('&nbsp;','',$lookupElement->streetAddress));
    }

    private function getQuerystring2($lookupElement){
        return urlencode('Rechtanwalt ' . $lookupElement->name);
    }

    private function getHtml($url,$dieIfError = true){
        $html = false;
        try{
            $ch = curl_init();

            $header = array("Connection: Keep-Alive", "User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36");
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	        curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 

            $response = curl_exec ($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close ($ch);
            $html = str_get_html($response);
            //echo $html;
            if(($html === FALSE) || ($httpCode != 200)){
                throw new Exception('error occurred');
            }
        }catch(Exception $e){
            echo PHP_EOL . "<br />FAILED to open page $url" . PHP_EOL;
            if($dieIfError){
                die;
            }
            return;
        }
        return $html;
    }
    
    private function parsePage($html, $lookupElement){
        if($html == false){
            return;
        }

        $quickResultBlock = $html
                ->find('div#rhs_block',0);
        if($quickResultBlock != null){

            if(($nameElement = $quickResultBlock->find('div._Q1n span', 0)) && empty($lookupElement->googlename)){
                $lookupElement->googlename = $nameElement->plaintext;
            }

            if(($addressElement = $quickResultBlock->find('div._G1d div._eFb span._Xbe', 0)) && empty($lookupElement->googleaddress)){
                $lookupElement->googleaddress = $addressElement->plaintext;
            }

            if(($phoneElement = $quickResultBlock->find('div._G1d div._eFb span._Xbe span span', 0)) && empty($lookupElement->googlephone)){
                $lookupElement->googlephone = $phoneElement->plaintext;
            }

            if(($websiteElement = $quickResultBlock->find('div._mdf div._ldf a.ab_button', 0)) && empty($lookupElement->googlewebsite)){
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

        $quickResultBlock = null;
    }
    
    private function parsePageAlternative($html, $lookupElement){
        if($html == false){
            return;
        }

        $quickResultBlock = $html
                ->find('div._M4k',0);
        if($quickResultBlock != null){
            $firstEl = $quickResultBlock->find('div',0);
            if($firstEl != null){
                if(($nameElement = $firstEl->find('div._rl', 0)) && empty($lookupElement->googlename)){
                    $lookupElement->googlename = $nameElement->plaintext;
                }

                if(($phoneElement = $firstEl->find('span.rllt__details div', 2)) && empty($lookupElement->googlephone)){
                    $lookupElement->googlephone = $phoneElement->plaintext;
                }

                if(($websiteElement = $firstEl->find('link', 0)) && empty($lookupElement->googlewebsite)){
                    $lookupElement->googlewebsite = $this->getStrippedWebsite(ltrim($websiteElement->href, " /url?"));
                }

                if(($lookupElement->googlewebsite) && empty($lookupElement->googlewebsitemetadescription) &&  empty($lookupElement->googlewebsitemetakeywords)){
                    $this->parseWebPage($lookupElement->googlewebsite,$lookupElement);
                }

            }
        }
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
        $result = $str;
        if(empty($result))
            return $result;
        
        parse_str($str,$parsedArr);
        if($parsedArr && isset($parsedArr['q'])){
            $result = $parsedArr['q'];
        }
        if(filter_var($result, FILTER_VALIDATE_URL))
            return $result;
        return '';
    }
}