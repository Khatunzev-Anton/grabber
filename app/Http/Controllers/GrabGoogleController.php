<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Repositories;
use Services;

class GrabGoogleController extends Controller
{
    private $__lookupRepo;
    private $__repo;
    private $__grabService;

    public function __construct(
            Repositories\ILookupRepository $lookupRepository, 
            Repositories\IRepository $repository, 
            Services\Grab\IGrabService $grabService
            ){
        $this->__lookupRepo = $lookupRepository;
        $this->__repo = $repository;
        $this->__grabService = $grabService;
    }

    public function Grab(Request $request){
        
        $lookupArr = $this->__lookupRepo->get(10000);
        //FIX THIS CRAP LATER!!!
        //$lookupArr = $this->__lookupRepo->GetFiltered(10000, 'id > (SELECT MAX("placeId") AS id from lawers)');
        $recordsCount = 0;
        $lookupsCount = 0;
        
        ob_end_flush();
        
        echo PHP_EOL . "<br />Google parsing started";

        foreach($lookupArr as $_lookupEl){    
            $resultsArr = $this->__grabService->parse($_lookupEl);
            $this->__repo->save($resultsArr);

            $recordsCount+= count($resultsArr);
            $lookupsCount++;

            echo PHP_EOL . "<br />lookup $lookupsCount finished. Total records: $recordsCount";

            $resultsArr = null;
        }

        $lookupArr = null;

        return PHP_EOL . "<br /><br/>Complete!<br/>Records Count:$recordsCount<br/>Lookups:$lookupsCount";
    }
}
