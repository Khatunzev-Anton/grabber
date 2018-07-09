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
        
        $lookupArr = $this->__lookupRepo->GetFiltered(100000,'id not in (select distinct placeid from lawyersgoogle) and id > 1072 order by id');
        //FIX THIS CRAP LATER!!!
        $recordsCount = 0;
        $currentLookup = 0;
        
        ob_end_flush();
        
        echo PHP_EOL . "<br />Google parsing started";

        foreach($lookupArr as $_lookupEl){    
            $resultsArr = $this->__grabService->parse($_lookupEl);
            $this->__repo->save($resultsArr);

            $recordsCount+= count($resultsArr);
            $currentLookup++;

            echo PHP_EOL . "<br />lookup $currentLookup finished. Total records: $recordsCount";

            $resultsArr = null;
        }

        $lookupArr = null;

        return PHP_EOL . "<br /><br/>Complete!<br/>Records Count:$recordsCount<br/>Lookups:$currentLookup";
    }

    public function GrabEmails(Request $request){
        $lookupArr = $this->__repo->GetFiltered(100000,'parsedwithgoogle IS NOT TRUE AND  char_length(case when email is null then \'\' else email end) = 0 AND char_length(case when website is null then \'\' else website end) > 3 order by id');
        $recordsCount = 0;

        ob_end_flush();
        
        echo PHP_EOL . "<br />Google parsing started";

        foreach($lookupArr as $_lookupEl){    
            if(empty($_lookupEl->website))
                continue;
            $this->__grabService->parseWebsite($_lookupEl);
            $_lookupEl->save();

            $recordsCount++;
        }

        $lookupArr = null;

        return PHP_EOL . "<br /><br/>Complete!<br/>Records Count:$recordsCount";
    }
}
