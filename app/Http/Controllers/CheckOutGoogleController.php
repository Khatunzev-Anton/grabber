<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Repositories;
use Services;

class CheckOutGoogleController extends Controller
{
    private $__repo;
    private $__grabService;

    public function __construct(
            Repositories\IRepository $repository, 
            Services\Grab\IGrabService $grabService
            ){
        $this->__repo = $repository;
        $this->__grabService = $grabService;
    }


    public function CheckGoogle(Request $request){
        ob_end_flush();
        $entityArr = $this->__repo->GetFiltered(20,'parsedwithgoogle IS NOT TRUE');

        foreach($entityArr as $_lookupEl){    
            echo "going to scrape el#" . $_lookupEl->id . PHP_EOL;
            $this->__grabService->parse($_lookupEl);
            echo "scrapped" . PHP_EOL;
            $_lookupEl->save();
            echo "saved" . PHP_EOL;

        }
        return "Complete!";
    }
}
