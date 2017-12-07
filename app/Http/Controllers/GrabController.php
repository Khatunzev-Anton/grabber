<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Repositories;
use Services;

class GrabController extends Controller
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
        $lookupArr = $this->__lookupRepo->get(1000);
        $recordsCount = 0;
        $lookupsCount = 0;
        
        ob_end_flush();

        foreach($lookupArr as $_lookupEl){    
            $resultsArr = $this->__grabService->parse($_lookupEl);
            $this->__repo->save($resultsArr);

            $recordsCount+= count($resultsArr);
            $lookupsCount++;

            echo "<br/>lookup $lookupsCount finished. Total records: $recordsCount";

            $resultsArr = null;
        }

        $lookupArr = null;

        return "<br/>Complete!<br/>Records Count:$recordsCount<br/>Lookups:$lookupsCount";
    }
}
