<?php

namespace Services\Grab\Gelbeseiten;

use Services\Grab as Grab;
use Exception;

class GelbeseitenDeService implements Grab\IGrabService{
    
    private $baseUrl = 'https://www.gelbeseiten.de/anwalt/';
    private $pageUrl = '';

    private $usePaging = false;

    public function __construct($usePaging = true){
        $this->usePaging = $usePaging;
    }
    
    public function getUrl(){
        return $this->baseUrl;
    }

    public function parse($lookupElement){
         $parsedArr = [];
         $PARSED_BLOCK_COUNT = 15;
         $this->pageUrl = $this->baseUrl . ($lookupElement->city . ',,' . $lookupElement->postcode . ',,,umkreis-0');
         do{
             $parsedBlock = [];
             $parsedBlock = $this->parsePage($this->pageUrl,$lookupElement->id);
             $parsedArr = array_merge($parsedArr, $parsedBlock);
             if($this->usePaging){
                $this->pageUrl = $this->getNextPageUrl();
             }
         }while(($this->usePaging) && (count($parsedBlock) >= $PARSED_BLOCK_COUNT));
         return $parsedArr;
    }

    private function parsePage($url, $placeId){
        $resultArr = [];
        
        echo "<br/>...parsing $url";
        try{
            $html = @file_get_html($url);
            if($html === FALSE){
                throw new Exception('error occurred');
            }
        }catch(Exception $e){
            echo "<br/>failed to open page $url";
            return $resultArr;
        }
        $articles = $html
                ->find('html>body>div#gs_body>div.container>div.gs_inhalt>div.span-fluid-wide>div#gs_treffer>article.teilnehmer');
                /*->find('html',0)
                ->find('body',0)
                ->find('div#gs_body',0)
                ->find('div.container',0)
                ->find('div.gs_inhalt',0)
                ->find('div.span-fluid-wide',0)
                ->find('div#gs_treffer',0)
                ->find('article.teilnehmer');*/

        foreach($articles as $article) {
            $lawer = [
                'placeId' => null, 
                'uniqueId' => null, 
                'name' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
                'streetAddress' => null,
                'postCode' => null,
                'addressLocality' => null,
                'lat' => null,
                'lon' => null
                        ];

            if($uniqueIdentifierAttribute = $article->getAttribute ('id')){
                $lawer['uniqueId'] = intval(str_replace("teilnehmer_","",$uniqueIdentifierAttribute));
            }

            $lawer['placeId'] = $placeId;

            if($nameElement = $article->find('div.table div.a header div.name div.h2 a.teilnehmername span', 0)){
                $lawer['name'] = $nameElement->plaintext;
            }
            if($phoneElement = $article->find('div.table div.d ul.profile li.phone a.telefonnummer span.teilnehmertelefon span.text span.nummer', 0)){
                $lawer['phone'] = $phoneElement->plaintext;
            }
            if($emailElement = $article->find('div.table div.d ul.profile li.link_blue div.email a.link', 0)){
                $lawer['email'] = $this->getStrippedEmail($emailElement->href);
            }
            if($websiteElement = $article->find('div.table div.d ul.profile li.link_blue div.website a.link', 0)){
                $lawer['website'] = $websiteElement->href;
            }
            if(
                ($addressElement = $article->find('div.table div.c div.flex_adresse div.adresse address a', 0))
                &&
                (count($addressElement->children()) > 0) 
                ){
                    if($streetAddressElement = $addressElement->find('span[itemprop=streetAddress]',0))
                        $lawer['streetAddress'] = $streetAddressElement->plaintext;
                    if($postCodeElement = $addressElement->find('span[itemprop=postalCode]',0))
                        $lawer['postCode'] = $postCodeElement->plaintext;
                    if($addressLocalityElement = $addressElement->find('span[itemprop=addressLocality]',0))
                        $lawer['addressLocality'] = $addressLocalityElement->plaintext;
            }
            if($dataMapAttribute = $article->getAttribute ('data-map')){
                $dataMapAttributeObj = json_decode($dataMapAttribute);
                $lawer['lat'] = $dataMapAttributeObj->wgs84Lat;
                $lawer['lon'] = $dataMapAttributeObj->wgs84Long;
            }

            $resultArr[] = $lawer;
        }

        $html = null;
        $articles = null;

        return $resultArr;
    }

    private function getNextPageUrl(){
        $nextPageNumber = 2;       
        $pagingSegmentPattern = '/\/s[0-9]+$/';

        $parsedPageUrl = parse_url($this->pageUrl);
        preg_match($pagingSegmentPattern, $parsedPageUrl['path'], $pagingSegmentMatches);
        if(!empty($pagingSegmentMatches)){//not a first page
            preg_match('/[0-9]+$/',$pagingSegmentMatches[0],$pageNumberArr);
            if(!empty($pageNumberArr)){
                $nextPageNumber = intval($pageNumberArr[0]) + 1;
            }
        }

        if($nextPageNumber > 2){
            $nextPageUrl = preg_replace($pagingSegmentPattern, ('/s' . $nextPageNumber), $this->pageUrl);
        }else{
            $nextPageUrl = $this->pageUrl . ('/s' . $nextPageNumber);
        }
        return $nextPageUrl;
    }

    private function getStrippedEmail($str){
        $result = '';
        $result = preg_replace(['/\b(mailto:)/','/(\?.*)|(#.*)/'], ['',''], $str);
        return $result;
    }


}