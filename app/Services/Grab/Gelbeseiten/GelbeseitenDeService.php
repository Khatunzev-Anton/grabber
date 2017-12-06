<?php

namespace Services\Grab\Gelbeseiten;

use Services\Grab as Grab;

class GelbeseitenDeService implements Grab\IGrabService{
    
    private $baseUrl = 'gelbeseiten.de/anwalt/';
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
         $this->pageUrl = $this->baseUrl . $relativeUrl;
         do{
             //parse;
             $parsedBlock = $this->parsePage($this->pageUrl);
             $parsedArr = array_merge($parsedArr, $parsedBlock);
             if($this->usePaging){
                $this->pageUrl = $this->getNextPageUrl();
             }
         }while(($this->usePaging) && (count($parsedBlock) >= $PARSED_BLOCK_COUNT));
         return $parsedArr;
    }

    private function parsePage($url){
        $resultArr = [];
        /*****************/
        $resultArr[] = 'a';
        $resultArr[] = 'b';
        $resultArr[] = 'c';
        /*****************/
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
            $nextPageUrl = preg_replace($pagingSegmentPattern, ('\s' . $nextPageNumber), $this->pageUrl);
        }else{
            $nextPageUrl = $this->pageUrl . ('\s' . $nextPageNumber);
        }
        return $nextPageUrl;
    }


}