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
        
        //$entityArr = $this->__repo->GetFiltered(20,'id = 59061');
        $entityArr = $this->__repo->GetFiltered(20,'parsedwithgoogle IS NOT TRUE');

        foreach($entityArr as $_lookupEl){    
            echo PHP_EOL . "<br />going to scrape el#" . $_lookupEl->id . PHP_EOL;
            $this->__grabService->parse($_lookupEl);
            echo  PHP_EOL . "<br />scrapped" . PHP_EOL;
            $_lookupEl->save();
            echo  PHP_EOL . "<br />saved" . PHP_EOL;

        }
        return "<br />Complete!";
    }
}
