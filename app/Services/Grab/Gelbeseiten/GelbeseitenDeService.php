<?php

namespace Services\Grab\Gelbeseiten;


use Services\Grab as Grab;

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

    public function parse($relativeUrl){
         $parsedArr = [];
         $PARSED_BLOCK_COUNT = 15;
         ob_end_flush();
         $this->pageUrl = $this->baseUrl . $relativeUrl;
         $cnt = 0;
         do{
             //parse;
             echo "parse start";
             $parsedBlock = [];
             $parsedBlock = $this->parsePage($this->pageUrl);
             $parsedArr = array_merge($parsedArr, $parsedBlock);
             echo "$cnt:" . $this->pageUrl . PHP_EOL;
            $cnt++;
            break;
             if($this->usePaging){
                $this->pageUrl = $this->getNextPageUrl();
             }
         }while(($this->usePaging) && (count($parsedBlock) >= $PARSED_BLOCK_COUNT) && ($cnt < 7));
         return $parsedArr;
    }

    private function parsePage($url){
        $resultArr = [];
        
        $html = file_get_html($url);
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
            $lawer = [];
            $lawer['name'] = $article->find('div.table div.a header div.name div.h2 a.teilnehmername span', 0)->plaintext;
            $lawer['phone'] = $article->find('div.table div.d ul.profile li.phone a.telefonnummer span.teilnehmertelefon span.text span.nummer', 0)->plaintext;
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