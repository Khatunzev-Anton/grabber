<?php

namespace Services\Grab\GooglePlaces;

use Services\Grab as Grab;
use Exception;

class GooglePlacesService implements Grab\IGrabService{
    
    private $baseUrl = 'https://www.google.de/search';
    private $pageUrl = '';

    private $usePaging = false;
    private $PARSED_BLOCK_COUNT = 20;

    public function __construct($usePaging = true){
        $this->usePaging = $usePaging;
    }
    
    public function getUrl(){
        return $this->baseUrl;
    }

    public function parse($lookupElement){
         $parsedArr = [];
         $pageNumber = 0;
         $this->pageUrl = $this->baseUrl . '?q=' . $this->getQuerystring($lookupElement,$pageNumber);
         do{
             $parsedBlock = [];
             $parsedBlock = $this->parsePage($this->pageUrl,$lookupElement->id);
             $parsedArr = array_merge($parsedArr, $parsedBlock);
             if($this->usePaging){
                $pageNumber++;
                $this->pageUrl = $this->baseUrl . '?q=' . $this->getQuerystring($lookupElement,$pageNumber);
             }
             break;
         }while(($this->usePaging) && (count($parsedBlock) >= $this->PARSED_BLOCK_COUNT));
         return $parsedArr;
    }

    public function parseWebsite($lookupElement){
        try{
            if(empty($lookupElement->website) || (strlen($lookupElement->website) == 0)){
                throw new Exception("website is empty");
            }

            echo PHP_EOL . "<br />parsing record#" . $lookupElement->id . " website url:" . $lookupElement->website; 

            $websiteDomain = str_replace('www.','',parse_url(strtolower($lookupElement->website),PHP_URL_HOST));

            $html = $this->getLayoutString($lookupElement->website);
            $emailRegExp = '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}/';
            //$emailRegExp = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';
            if(preg_match_all($emailRegExp, $html, $out) > 0){
                //var_dump($out);
                foreach($out as $mailMatches){
                    $found = false;
                    foreach($mailMatches as $email){
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
                            continue;
                        if ($websiteDomain){
                            $emailDomain = substr(strrchr($email, "@"), 1);
                            if(strtolower(trim($websiteDomain)) != strtolower(trim($emailDomain))){
                                echo PHP_EOL . "<br />scrape email address " . $email . " belongs to different domain (not " . $websiteDomain . ")."; 
                                continue;
                            }
                        }
                        $lookupElement->email = $email;
                        $found = true;
                        echo PHP_EOL . "<br />record#" . $lookupElement->id . " PARSED. email:" . $lookupElement->email; 
                        break;
                    }
                    if($found)
                        break;
                }
            }
        }catch(Exception $e){
            echo PHP_EOL . "<br />FAILED to scrape record#" . $lookupElement->id . " due to exception:" . $e->getMessage(); 
        }finally{
            $lookupElement->parsedwithgoogle = true;
        }
    }

    private function getQuerystring($lookupElement, $pageNumber){
        return 'anwalt+' . $lookupElement->postcode . '&tbm=lcl&gws_rd=cr&dcr=0' . ($pageNumber > 0 ? ('&start=' . ($pageNumber * $this->PARSED_BLOCK_COUNT)) : '');
    }

    private function getLayoutString($url){
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

            $html = curl_exec ($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close ($ch);
            //echo $html;
            if($httpCode != 200){
                throw new Exception('error occurred');
            }
        }catch(Exception $e){
            echo PHP_EOL . "<br />FAILED to open page $url" . PHP_EOL;
            return;
        }
        return $html;
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

    private function parsePage($url, $placeId){
        $resultArr = [];
        
        echo PHP_EOL . "<br />...PARSING $url" . PHP_EOL;
        $html = $this->getHtml($url);
        
        $articles = $html
                ->find('div#rl_ist0>div.rlfl__tls>div._Db');

        echo PHP_EOL . "<br />articles count:" . count($articles)  . PHP_EOL;

        foreach($articles as $article) {
            $lawyerGoogle = [
                'placeid' => $placeId, 
                'uniqueid'=>null, 
                'name'=>null,
                'phone'=>null,
                'email'=>null,
                'website'=>null,
                'streetaddress'=>null,
                'rating'=>null,
                'reviews'=>null,
                'websitemetadescription'=>null,
                'websitemetakeywords'=>null,
                'websitepagescount'=>null,
                'websiteemail'=>null,
                'websitephone'=>null
                        ];

            if($uniqueIdentifierElement = $article->find('a._sEo', 0)){
                $lawyerGoogle['uniqueid'] = $uniqueIdentifierElement->getAttribute ('data-cid');
            }

            if($nameElement = $article->find('a._sEo div._iPk div._rl', 0)){
                $lawyerGoogle['name'] = $nameElement->plaintext;
            }

            if(($addInfoContainerCnt = count($article->find('a._sEo span.rllt__details>div')) )> 0){
                if($ratingElement = $article->find('a._sEo span.rllt__details>div', 0)->find('span._PXi',0)){
                    $lawyerGoogle['rating'] = str_replace(',','.',$ratingElement->plaintext);
                }

                if($reviewsElement = $article->find('a._sEo span.rllt__details>div', 0)){
                    preg_match('/\([0-9]+\)/',$reviewsElement->plaintext,$matches);
                    if(count($matches) > 0)
                        $lawyerGoogle['reviews'] = trim(trim($matches[0],"("),")");
                }
                
                ////////////////
                if(($addInfoContainerCnt > 0) && ($lastDivElement = $article->find('a._sEo span.rllt__details>div', $addInfoContainerCnt - 1)->find('span',0))){
                    if($this->isValidPhoneNumber($lastDivElement->plaintext)){
                        $lawyerGoogle['phone'] = $lastDivElement->plaintext;

                        if(($addInfoContainerCnt > 1) && ($addressElement = $article->find('a._sEo span.rllt__details>div', $addInfoContainerCnt - 2)->find('span',0))){
                            $lawyerGoogle['streetaddress'] = $addressElement->plaintext;
                        }
                    }else{
                        if(($addInfoContainerCnt > 1) && ($penaltimateDivElement = $article->find('a._sEo span.rllt__details>div', $addInfoContainerCnt - 2)->find('span',0))){
                            if($this->isValidPhoneNumber($penaltimateDivElement->plaintext)){
                                $lawyerGoogle['phone'] = $penaltimateDivElement->plaintext;
                            }else{
                                $lawyerGoogle['streetaddress'] = $penaltimateDivElement->plaintext;
                            }
                        }
                    }
                }
            }

            if($websiteElement = $article->find('a._jlf', 0)){
                $lawyerGoogle['website'] = $this->getStrippedURL($websiteElement->href);
            }

            if($lawyerGoogle['website']){
                $this->parseWebPage($lawyerGoogle['website'],$lawyerGoogle);
            }

            $resultArr[] = $lawyerGoogle;
        }

        $html = null;
        $articles = null;

        echo PHP_EOL . "<br/>parsed";

        return $resultArr;
    }

    private function isValidPhoneNumber($phoneNumber){
        if(!preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', trim($phoneNumber))) {
            return false;
        } 
        return true;
    }

    private function getStrippedURL($str){
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

    private function getStrippedEmail($str){
        $result = '';
        $result = preg_replace(['/\b(mailto:)/','/(\?.*)|(#.*)/'], ['',''], $str);
        return $result;
    }

    private function parseWebPage($url, &$lookupElement){
        echo PHP_EOL . "<br />....webpage parsing: $url" . PHP_EOL;
        $html = $this->getHtml($url,false);
        if(!$html){
            return;
        }
        if($descriptionElement = $html->find('meta[name=description]', 0)){
            if(@mb_check_encoding($descriptionElement->content, "UTF-8")){
                $lookupElement['websitemetadescription'] = @$descriptionElement->content;
            }else{
                echo PHP_EOL . "<br />!!!INVALID ENCODING(description): " . @$descriptionElement->content . PHP_EOL;
            }
        }

        if($keywordsElement = $html->find('meta[name=keywords]', 0)){
            if(@mb_check_encoding($keywordsElement->content, "UTF-8")){
                $lookupElement['websitemetakeywords'] = @$keywordsElement->content;
            }else{
                echo PHP_EOL . "<br />!!!INVALID ENCODING(keyword): " . @$keywordsElement->content . PHP_EOL;
            }
        }
        echo PHP_EOL . "<br />WEBPAGE PARSED";
        $html = null;
    }


}